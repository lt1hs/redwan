import { boot } from 'quasar/wrappers';
import { Quasar } from 'quasar';
import arLocale from 'quasar/lang/ar';

// Add Arabic locale to Quasar
Quasar.lang.set(arLocale);

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  // Set up Arabic locale for Quasar components
  // This ensures QDate and other components use the proper locale formatting
  
  // You can also add additional i18n plugins here if needed
}); 