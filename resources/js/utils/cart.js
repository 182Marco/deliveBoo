// Pure helpers on the cart: an array with one entry per portion
const countPortions = (cart, plate) => cart.filter(e => e.id == plate.id).length;

// first portion of every plate, in cart order
const uniquePlates = cart =>
    cart.reduce((acc, cur) => (acc.some(e => e.id === cur.id) ? acc : [...acc, cur]), []);

const cartTotal = cart => cart.reduce((acc, cur) => acc + cur.price, 0);

// a single order can contain plates of one restaurant only
const canAddPlate = (cart, plate) =>
    !cart.length || cart.some(e => e.restaurant_id == plate.restaurant_id);

export { countPortions, uniquePlates, cartTotal, canAddPlate };
