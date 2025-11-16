declare module 'quasar/wrappers' {
  import type { App } from 'vue';

  type BootCallback = (app: App) => void | Promise<void>;
  type BootFunction = (callback: BootCallback) => BootCallback;

  export const boot: BootFunction;
}
