const path = require('path'); // Add this line

module.exports = function (ctx) {
  return {
    // ... other config options ...

    build: {
      alias: {
        // Add this alias
        'boot': path.resolve(__dirname, './src/boot')
      }
    },

    boot: [
      'axios',
      'i18n'
      // ... other boot files ...
    ]

    // ... rest of the config ...
  };
};
