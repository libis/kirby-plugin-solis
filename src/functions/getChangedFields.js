export function getChangedFields(original, current) {
  const changed = {};

  for (const key in current) {
    const oldVal = original[key];
    const newVal = current[key];

    const isDifferent = JSON.stringify(oldVal) !== JSON.stringify(newVal);

    if (isDifferent) {
      changed[key] = newVal;
    }
  }

  return changed;
}