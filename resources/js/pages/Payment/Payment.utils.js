// customer fields of the order form: keys are the names expected by POST /api/orders
const CUSTOMER_FIELDS = [
    { key: "customer_name", label: "Name", type: "text", minlength: 3, maxlength: 20 },
    { key: "customer_lastName", label: "Last name", type: "text", minlength: 3, maxlength: 20 },
    { key: "customer_email", label: "Email", type: "email" },
    { key: "customer_phone", label: "Phone", type: "text", minlength: 9, maxlength: 11 },
    { key: "customer_address", label: "Address", type: "text", minlength: 3 }
];

const emptyCustomer = () => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, ""]));

// picks the customer fields out of any object (the component data)
const pickCustomer = source => Object.fromEntries(CUSTOMER_FIELDS.map(e => [e.key, source[e.key]]));

const isCustomerComplete = customer => CUSTOMER_FIELDS.every(e => customer[e.key]);

// the cart holds plates of one restaurant only: the first one gives the restaurant
const buildOrder = (cart, total, customer) => ({
    restaurant_id: cart[0].restaurant_id,
    price: total,
    ...customer,
    plates: cart.map(e => e.id)
});

export { CUSTOMER_FIELDS, emptyCustomer, pickCustomer, isCustomerComplete, buildOrder };
