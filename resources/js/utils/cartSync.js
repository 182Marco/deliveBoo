// Serial queue of the cart writes sent to the server (dev/PIANO_CARRELLO.md): one request at a time,
// in order. A job pushed with the key of a job still waiting replaces it, so a burst of clicks on the
// same plate sends only the last quantity. A reset job (empty the cart) drops every waiting job:
// it supersedes them. Jobs handle their own errors; the queue only runs them.
const createCartQueue = () => {
    const waiting = new Map();
    let running = null;

    const drain = async () => {
        while (waiting.size) {
            const [key, job] = waiting.entries().next().value;
            waiting.delete(key);
            await job();
        }
        running = null;
    };

    const push = (key, job, { reset = false } = {}) => {
        if (reset) waiting.clear();
        waiting.set(key, job);
        running ??= drain();
        return running;
    };

    // resolves once nothing is waiting or running
    const idle = () => running ?? Promise.resolve();

    const busy = () => running !== null;

    return { busy, idle, push };
};

// lines of a server snapshot, in the shape of cartLines: { plate, quantity }
const snapshotLines = snapshot => (snapshot?.items ?? []).map(({ plate, quantity }) => ({ plate, quantity }));

// the confirmed lines with the changes still on their way ({ [plateId]: { plate, quantity } }) on top:
// changed quantities in place, 0 removes, new plates at the end in the order they were added
const withOverrides = (lines, overrides) => {
    const changed = lines
        .map(line => (overrides[line.plate.id] ? { ...line, quantity: overrides[line.plate.id].quantity } : line))
        .filter(line => line.quantity > 0);
    const added = Object.values(overrides)
        .filter(({ plate, quantity }) => quantity > 0 && !lines.some(line => line.plate.id == plate.id))
        .map(({ plate, quantity }) => ({ plate, quantity }));
    return [...changed, ...added];
};

// a newer snapshot of the same owner, or one of another owner (login, logout, expired session)
const isNewerSnapshot = (current, next) =>
    !current || current.owner !== next.owner || next.version >= current.version;

export { createCartQueue, isNewerSnapshot, snapshotLines, withOverrides };
