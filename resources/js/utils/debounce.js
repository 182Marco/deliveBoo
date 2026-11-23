// delays `fn` until `wait` ms have passed without a new call: only the last arguments win.
// `cancel()` drops the pending call (e.g. the field was emptied or the component unmounted).
const debounce = (fn, wait) => {
    let timer = null;
    const debounced = (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
    debounced.cancel = () => clearTimeout(timer);
    return debounced;
};

export { debounce };
