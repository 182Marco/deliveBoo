// Braintree hosted fields (client side only: the nonce is not charged server side)
import braintree from "braintree-web";

const AUTHORIZATION = "sandbox_rzpfyzvh_dgt5rntdphpdzyhh";

const HOSTED_FIELDS_OPTIONS = {
    styles: {
        input: {
            "font-size": "14px",
            "font-family": "Open Sans"
        }
    },
    fields: {
        number: { selector: "#creditCardNumber", placeholder: "Enter Credit Card" },
        cvv: { selector: "#cvv", placeholder: "Enter CVV" },
        expirationDate: { selector: "#expireDate", placeholder: "00 / 0000" }
    }
};

const createHostedFields = async () => {
    const client = await braintree.client.create({ authorization: AUTHORIZATION });
    return braintree.hostedFields.create({ client, ...HOSTED_FIELDS_OPTIONS });
};

export { createHostedFields };
