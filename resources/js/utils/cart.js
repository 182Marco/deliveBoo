// Pure helpers on the cart. Storage today: an array with one entry per portion.
// Components never read that array: they use the store getters built on cartLines,
// so the storage can change (dev/PIANO_CARRELLO.md) without touching them.
const countPortions = (cart, plate) => cart.filter(e => e.id == plate.id).length;

// first portion of every plate, in cart order
const uniquePlates = cart =>
    cart.reduce((acc, cur) => (acc.some(e => e.id === cur.id) ? acc : [...acc, cur]), []);

// one line per plate, in cart order: { plate, quantity }
const cartLines = cart => uniquePlates(cart).map(plate => ({ plate, quantity: countPortions(cart, plate) }));

const linesTotal = lines => lines.reduce((acc, { plate, quantity }) => acc + quantity * plate.price, 0);

const linesCount = lines => lines.reduce((acc, { quantity }) => acc + quantity, 0);

// restaurant of the lines ({ id, slug }), null for an empty cart
const linesRestaurant = lines =>
    lines.length ? { id: lines[0].plate.restaurant_id, slug: lines[0].plate.restaurant_slug } : null;

// a single order can contain plates of one restaurant only
const canAddPlate = (restaurant, plate) => !restaurant || restaurant.id == plate.restaurant_id;

export { canAddPlate, cartLines, countPortions, linesCount, linesRestaurant, linesTotal, uniquePlates };
