// Card form of the Payment page: the server decides the driver (PAYMENT_DRIVER) and,
// for Braintree, generates the client token. Both drivers expose { tokenize } -> { nonce }.
// braintree-web (~400 kB) is a separate chunk, downloaded only when the driver needs it.
import axios from "axios";
import { createFakeHostedFields } from "./fakeHostedFields.js";

const fetchPaymentConfig = async () => (await axios.get("/api/payments/config")).data;

const createCardForm = async () => {
    const { driver, authorization, testMode = false } = await fetchPaymentConfig();
    const fields =
        driver === "braintree"
            ? await (await import("./braintree.js")).createHostedFields(authorization)
            : await createFakeHostedFields();
    return { driver, testMode, fields };
};

export { createCardForm };
