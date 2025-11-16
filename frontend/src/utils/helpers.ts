/**
 * Pick a list of properties from an object
 * into a new object
 */
// https://github.com/rayepps/radash/blob/069b26cdd7d62e6ac16a0ad3baa1c9abcca420bc/src/object.ts#L170
export const pick = <T extends object, TKeys extends keyof T>(
  obj: T,
  keys: TKeys[]
): Pick<T, TKeys> => {
  if (!obj) return {} as Pick<T, TKeys>;
  return keys.reduce(
    (acc, key) => {
      if (Object.prototype.hasOwnProperty.call(obj, key)) acc[key] = obj[key];
      return acc;
    },
    {} as Pick<T, TKeys>
  );
};

export function currencyFormat(value: number, currencyCode: string = 'IQD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currencyCode,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
    // trailingZeroDisplay: "stripIfInteger", // TODO: Use this only instead minimumFractionDigits and maximumFractionDigits
  }).format(value);
}

export function numberFormat(value: number) {
  return new Intl.NumberFormat('en-US').format(value || 0);
}
