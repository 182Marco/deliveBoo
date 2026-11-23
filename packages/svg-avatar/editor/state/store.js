// Minimal store: synchronous reducer, change listeners. No DOM, no globals.
const createStore = (reducer, initialState) => {
    let state = initialState;
    const listeners = new Set();
    return {
        getState: () => state,
        dispatch: action => {
            const next = reducer(state, action);
            if (next !== state) {
                const previous = state;
                state = next;
                listeners.forEach(listener => listener(state, previous, action));
            }
            return state;
        },
        subscribe: listener => {
            listeners.add(listener);
            return () => listeners.delete(listener);
        }
    };
};

export { createStore };
