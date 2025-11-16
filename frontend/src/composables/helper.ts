import axios from 'axios';
import { exportFile, useQuasar } from 'quasar';

export function useHelper() {
  const $q = useQuasar();

  const handleServerError = (error: any) => {
    console.error('Server Error:', error);
    console.error('Response:', error.response?.data);

    if (error.response?.status === 422) {
      // Handle the case where we have a message and errors string
      if (error.response.data?.message && error.response.data?.errors) {
        const errorMessage = error.response.data.errors;
        if (typeof errorMessage === 'string') {
          // Split the error message if it contains multiple errors
          const errors = errorMessage.includes(' (and ')
            ? errorMessage.split(' (and ')[0] // Take only the first error
            : errorMessage;

          $q?.notify({
            type: 'warning',
            message: errors,
            position: 'top',
            timeout: 3000
          });
          return;
        }
      }

      const errors = error.response.data.errors;

      // Handle array format
      if (Array.isArray(errors)) {
        errors.forEach((message: string) => {
          $q?.notify({
            type: 'warning',
            message,
            position: 'top',
            timeout: 3000
          });
        });
        return;
      }

      // Handle object format
      if (typeof errors === 'object' && errors !== null) {
        Object.entries(errors).forEach(([field, messages]) => {
          const messageArray = Array.isArray(messages) ? messages : [messages];
          messageArray.forEach((message: string) => {
            $q?.notify({
              type: 'warning',
              message: message,
              position: 'top',
              timeout: 3000
            });
          });
        });
        return;
      }

      // Handle string format
      if (typeof errors === 'string') {
        $q?.notify({
          type: 'warning',
          message: errors,
          position: 'top',
          timeout: 3000
        });
        return;
      }
    }

    // Handle general errors
    $q?.notify({
      type: 'negative',
      message: error.response?.data?.message || 'حدث خطأ في الخادم',
      position: 'top',
      timeout: 3000
    });
  };

  function imageFileToBase64(file: File, maximum_size = 1280) {
    return new Promise((resolve) => {
      const reader = new FileReader();

      reader.onload = (ev) => {
        if (!ev.target) return;
        const image = new Image();

        image.src = ev.target.result as string;

        image.addEventListener('load', function () {
          const c = document.createElement('canvas');
          const ctx = c.getContext('2d');
          if (!ctx) return;
          if (this.naturalWidth > maximum_size || this.naturalHeight > maximum_size) {
            if (this.naturalWidth > this.naturalHeight) {
              c.width = maximum_size;
              c.height = (maximum_size * this.naturalHeight) / this.naturalWidth;
            } else {
              c.height = maximum_size;
              c.width = (maximum_size * this.naturalWidth) / this.naturalHeight;
            }
          } else {
            c.width = this.naturalWidth;
            c.height = this.naturalHeight;
          }
          ctx.drawImage(this, 0, 0, c.width, c.height);

          resolve(c.toDataURL('image/jpeg', 0.8));
        });
      };

      reader.readAsDataURL(file);
    });
  }

  function imageFileInputToBase64(maximum_size = 1280) {
    return new Promise((resolve) => {
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';

      fileInput.addEventListener('change', async () => {
        if (fileInput.files && fileInput.files.length == 1) {
          const photo = fileInput.files[0];
          fileInput.remove();
          resolve(await imageFileToBase64(photo, maximum_size));
        }
      });

      fileInput.click();
    });
  }

  function wrapCsvValue(val: any, formatFn: any = null, row: any = null) {
    let formatted = formatFn !== void 0 ? formatFn(val, row) : val;

    formatted = formatted === void 0 || formatted === null ? '' : String(formatted);

    formatted = formatted.split('"').join('""');
    /**
     * Excel accepts \n and \r in strings, but some other CSV parsers do not
     * Uncomment the next two lines to escape new lines
     */
    // .split('\n').join('\\n')
    // .split('\r').join('\\r')

    return `"${formatted}"`;
  }

  function exportTable(columns: any, rows: any) {
    // naive encoding to csv format
    const content = [columns.map((col: any) => wrapCsvValue(col.label))]
      .concat(
        rows.map((row: any) =>
          columns
            .map((col: any) =>
              wrapCsvValue(
                typeof col.field === 'function'
                  ? col.field(row)
                  : row[col.field === void 0 ? col.name : col.field],
                col.format,
                row
              )
            )
            .join(',')
        )
      )
      .join('\r\n');

    const status = exportFile('table-export.csv', content, 'text/csv');

    if (status !== true) {
      $q.notify({
        message: 'Browser denied file download...',
        color: 'negative',
        icon: 'warning'
      });
    }
  }

  return {
    handleServerError,
    imageFileToBase64,
    imageFileInputToBase64,
    exportTable
  };
}
