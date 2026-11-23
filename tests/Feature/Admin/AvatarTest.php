<?php

namespace Tests\Feature\Admin;

use App\Avatar\AvatarCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** The partner header shows the user's avatar placeholder, drawn in the browser by jsBack/avatar.js. */
class AvatarTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    public function testHeaderHasAvatarPlaceholderWithSeedAndInitial()
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->get(route('admin.restaurants.index'))
            ->assertOk()
            ->assertSee('data-avatar-seed="user-'.$owner->id.'"', false)
            ->assertSee('data-avatar-overrides="{}"', false)
            ->assertSee('>'.mb_strtoupper(mb_substr($owner->name, 0, 1)).'</span>', false);
    }

    public function testGuestsDoNotLoadTheAvatarScript()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-avatar-seed', false);
    }

    public function testMenuLinksToTheEditorPage()
    {
        $this->actingAs($this->owner())
            ->get(route('admin.restaurants.index'))
            ->assertSee(route('admin.avatar.edit'), false)
            ->assertSee(__('partner.header.customizeAvatar'));
    }

    public function testGuestsCannotOpenOrSaveTheEditor()
    {
        $this->get(route('admin.avatar.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.avatar.update'), ['seed' => 'abc', 'overrides' => '{}'])->assertRedirect(route('login'));
    }

    public function testEditShowsTheCurrentValueSafelyEscaped()
    {
        $owner = $this->owner();
        $owner->avatar = ['seed' => '"><script>alert(1)</script>', 'overrides' => ['hair' => 'bun']];
        $owner->save();

        $html = $this->actingAs($owner)->get(route('admin.avatar.edit'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringContainsString('data-avatar-editor', $html);
        // the JSON is HTML-escaped inside the attribute and decodes back to the stored value
        preg_match('/data-value="([^"]*)"/', $html, $match);
        $value = json_decode(html_entity_decode($match[1], ENT_QUOTES), true);
        $this->assertSame(['seed' => '"><script>alert(1)</script>', 'overrides' => ['hair' => 'bun']], $value);
        preg_match('/data-labels="([^"]*)"/', $html, $labels);
        $this->assertSame('Hairstyle', json_decode(html_entity_decode($labels[1], ENT_QUOTES), true)['traits']['hair']);
    }

    public function testUpdateSavesAValidAvatarAndGoesBackToThePreviousPartnerPage()
    {
        $owner = $this->owner();
        $from = route('admin.plates.index', $owner->restaurants()->first()->slug);

        $this->actingAs($owner)->withHeader('referer', $from)->get(route('admin.avatar.edit'))->assertOk();
        $this->put(route('admin.avatar.update'), [
            'seed' => 'my-seed_1',
            'overrides' => json_encode(['hair' => 'bun', 'nose' => 'button', 'skinColor' => 'deep']),
        ])->assertRedirect($from)->assertSessionHas('avatarSaved');

        $owner->refresh();
        $this->assertSame('my-seed_1', $owner->avatarSeed());
        $this->assertSame(['hair' => 'bun', 'nose' => 'button', 'skinColor' => 'deep'], $owner->avatarOverrides());

        $this->get($from)->assertSee(__('partner.avatar.saved'));
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidPayloads(): array
    {
        return [
            'missing seed' => [['overrides' => '{}']],
            'malformed seed' => [['seed' => 'bad seed!', 'overrides' => '{}']],
            'seed too long' => [['seed' => str_repeat('a', 65), 'overrides' => '{}']],
            'overrides not json' => [['seed' => 'abc', 'overrides' => '{hair:bun']],
            'overrides is a list' => [['seed' => 'abc', 'overrides' => '["bun"]']],
            'overrides is a string' => [['seed' => 'abc', 'overrides' => '"bun"']],
            'unknown trait' => [['seed' => 'abc', 'overrides' => '{"wings":"big"}']],
            'unknown value' => [['seed' => 'abc', 'overrides' => '{"hair":"mohawk"}']],
            'unknown colour' => [['seed' => 'abc', 'overrides' => '{"skinColor":"#000000"}']],
            'non-string value' => [['seed' => 'abc', 'overrides' => '{"hair":["bun"]}']],
            'forbidden pair' => [['seed' => 'abc', 'overrides' => '{"hair":"afro","accessory":"beanie"}']],
            'too many keys' => [['seed' => 'abc', 'overrides' => json_encode(array_fill_keys(array_map(fn ($i) => "k$i", range(1, 15)), 'x'))]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloads')]
    public function testUpdateRejectsTamperedPayloads(array $payload)
    {
        $owner = $this->owner();
        $owner->avatar = ['seed' => 'kept', 'overrides' => []];
        $owner->save();

        $this->actingAs($owner)
            ->from(route('admin.avatar.edit'))
            ->put(route('admin.avatar.update'), $payload)
            ->assertRedirect(route('admin.avatar.edit'))
            ->assertSessionHasErrors();

        $this->assertSame('kept', $owner->fresh()->avatarSeed());
    }

    public function testRejectedSubmitReopensTheEditorOnTheSubmittedValue()
    {
        $this->actingAs($this->owner())
            ->from(route('admin.avatar.edit'))
            ->put(route('admin.avatar.update'), ['seed' => 'abc', 'overrides' => '{"hair":"afro","accessory":"beanie"}']);

        $this->get(route('admin.avatar.edit'))
            ->assertSee(__('partner.avatar.invalid'))
            ->assertSee('data-avatar-unsaved', false)
            ->assertSee('&quot;seed&quot;:&quot;abc&quot;', false);
    }

    public function testSavingNeverTouchesOtherUsers()
    {
        $owner = $this->owner();
        $other = \App\User::where('id', '!=', $owner->id)->first();
        $before = $other->avatar;

        $this->actingAs($owner)->put(route('admin.avatar.update'), ['seed' => 'mine', 'overrides' => '{}'])->assertRedirect();

        $this->assertSame($before, $other->fresh()->avatar);
        $this->assertSame('mine', $owner->fresh()->avatarSeed());
    }

    /** @return array<string, array{0: string}> */
    public static function unsafeReferers(): array
    {
        return [
            'external site' => ['https://evil.test/admin/restaurants'],
            'protocol-relative' => ['//evil.test/admin'],
            'outside the partner area' => ['http://localhost/login'],
            'the editor itself' => ['http://localhost/admin/avatar'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsafeReferers')]
    public function testUnsafePreviousPagesFallBackToTheRestaurantList(string $referer)
    {
        $this->actingAs($this->owner())->withHeader('referer', $referer)->get(route('admin.avatar.edit'))
            ->assertSee('href="'.route('admin.restaurants.index').'"', false);

        $this->put(route('admin.avatar.update'), ['seed' => 'abc', 'overrides' => '{}'])
            ->assertRedirect(route('admin.restaurants.index'));
    }

    public function testCatalogMatchesTheSharedOptionsFixture()
    {
        $fixture = json_decode(file_get_contents(base_path('packages/svg-avatar/tests/fixtures/options.json')), true);

        $this->assertSame($fixture['traits'], AvatarCatalog::options());
        $this->assertSame($fixture['excludedPairs'], AvatarCatalog::excludedPairs());
        $this->assertContains('button', AvatarCatalog::options()['nose']);
    }

    public function testOverridesKeepOnlyKnownTraitsAndValues()
    {
        $owner = $this->owner();
        $owner->avatar = ['seed' => 'abc', 'overrides' => [
            'hair' => 'bun', 'skinColor' => 'deep', 'hair2' => 'bun', 'eyes' => 'laser', 'mouth' => ['smile'],
        ]];
        $owner->save();

        $this->assertSame('abc', $owner->fresh()->avatarSeed());
        $this->assertSame(['hair' => 'bun', 'skinColor' => 'deep'], $owner->fresh()->avatarOverrides());
    }

    public function testCatalogReadsThePackageData()
    {
        $options = AvatarCatalog::options();

        $this->assertSame(['face', 'hair', 'eyes', 'brows', 'mouth', 'clothes', 'accessory', 'nose',
            'bgColor', 'skinColor', 'hairColor', 'clothColor', 'accentColor', 'irisColor'], array_keys($options));
        $this->assertCount(12, $options['hair']);
        $this->assertContains('none', $options['accessory']);
        $this->assertContains('deep', $options['skinColor']);
        $this->assertSame([], AvatarCatalog::sanitizeOverrides('not an array'));
    }
}
