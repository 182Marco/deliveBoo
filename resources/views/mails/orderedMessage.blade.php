<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
		<style>
			* {
				font-family: sans-serif !important;
			}
		</style>

    <style type="text/css">
        html,
        body {
	        margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        /* What is does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] {
            margin:0 !important;
        }
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            Margin: 0 auto !important;
        }
				.shadow-table{
					box-shadow: rgb(0 0 0 / 24%) 0px 3px 8px;
				}
        table table table {
            table-layout: auto;
        }
        img {
            -ms-interpolation-mode:bicubic;
        }
        .mobile-link--footer a,
        a[x-apple-data-detectors] {
            color:inherit !important;
            text-decoration: underline !important;
        }
    </style>

    <style>
        .button-td,
        .button-a {
            transition: all 100ms ease-in;
        }
        .button-td:hover,
        .button-a:hover {
            background: #555555 !important;
            border-color: #555555 !important;
        }
        /* Media Queries */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: auto !important;
                box-shadow: rgb(0 0 0 / 24%) 0px 3px 8px;
            }
            .fluid,
            .fluid-centered {
                max-width: 100% !important;
                height: auto !important;
                Margin-left: auto !important;
                Margin-right: auto !important;
            }
            .fluid-centered {
                Margin-left: auto !important;
                Margin-right: auto !important;
            }
            .stack-column,
            .stack-column-center {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                direction: ltr !important;
            }
            .stack-column-center {
                text-align: center !important;
            }
            .center-on-narrow {
                text-align: center !important;
                display: block !important;
                Margin-left: auto !important;
                Margin-right: auto !important;
                float: none !important;
            }
            table.center-on-narrow {
                display: inline-block !important;
            }
        }
    </style>

</head>
<body bgcolor="#E0F3F9" width="100%" style="Margin: 0;">
    <center style="width: 100%; background: #E0F3F9;">



        <table cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" width="600" style="margin: auto;" class="email-container">

        <tr>
				<td>
					<img src="https://static-www.quotidianopiemontese.it/wp-content/2021/01/Delivero-posate.jpg" width="600" height="" alt="alt_text" border="0" align="center" style="width: 100%; max-width: 600px;">
				</td>
        </tr>

            <tr>
                <td style="padding: 40px; text-align: center; font-family: sans-serif; font-size: 15px; mso-height-rule: exactly; line-height: 20px; color:#2E3333;">
                    <h1>Thank You {{  $contact['customer_name']}}!</h1>
                    Your favorite food is on its way!
                    <br><br>
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="padding: 10px;">
                    <table cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            
                            <td class="stack-column-center">
                                <table cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="padding: 10px; text-align: center">
                                            <img src="https://www.cookipedia.co.uk/wiki/images/thumb/1/17/Mini_bangers_and_mash_party_food_recipe.jpg/300px-Mini_bangers_and_mash_party_food_recipe.jpg" width="270" height="270" alt="alt_text" border="0" class="fluid">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-family: sans-serif; font-size: 15px; mso-height-rule: exactly; line-height: 20px; color: #555555; padding: 0 10px 10px; text-align: left;" class="center-on-narrow">
                                            The food you love, delivered to your home!
                                        </td>
                                    </tr>
                                </table>
                            </td>
                     
                            <td class="stack-column-center">
                                <table cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="padding: 10px; text-align: center">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Eq_it-na_pizza-margherita_sep2005_sml.jpg/300px-Eq_it-na_pizza-margherita_sep2005_sml.jpg" width="270" height="270" alt="alt_text" border="0" class="fluid">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-family: sans-serif; font-size: 15px; mso-height-rule: exactly; line-height: 20px; color: #555555; padding: 0 10px 10px; text-align: left;" class="center-on-narrow">
                                            The great classics that warm the heart, perfect at any time!
                                        </td>
                                    </tr>
                                </table>
                            </td>
                
                        </tr>
                    </table>
                </td>
            </tr>
<tr>
    <td style="padding: 40px; text-align: center; font-family: sans-serif; font-size: 15px; mso-height-rule: exactly; line-height: 20px; color:#2E3333;">
        <strong>Delivering to: </strong> {{  $contact['customer_address']}}.
        <br><br>
        <h2>Thank you for choosing Deliveroo!</h2>
        <br><br>
        @component('mail::button', ['url' => 'http://127.0.0.1:8000'])
Deliveroo
@endcomponent
        </table>
    </td>
</tr>
    </center>
</body>
</html>











{{-- @component('mail::message')
# Thank You {{  $contact['customer_name']}}!

## Your favorite food is on its way!
**Delivering to: **{{  $contact['customer_address']}}.

**Total: ** {{  $contact['price']}}.



@component('mail::button', ['url' => ''])
View Order
@endcomponent

**Thank you for choosing Deliveroo!**

{{ config('app.name') }}
@endcomponent






{{-- Name --}}
{{-- <h1></h1>
<h2></h2>


<div>
    <strong>Delivering to: </strong> {{  $contact['customer_address']}}.
</div>


<div>
    <strong>Total: </strong> 
</div>
<div>
    <strong>Thank you for choosing Deliveroo!</strong>
</div> --}}
