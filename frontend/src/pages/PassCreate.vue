<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePassportsStore } from '@/stores/passports';
import PassForm from '@/components/PassForm.vue';
import { useQuasar } from 'quasar';
import moment from 'moment';
import axios from 'axios';
import { SmsService } from '@/services/sms';

const router = useRouter();
const passports = usePassportsStore();
const loading = ref(false);
const $q = useQuasar();

// Function to send SMS notification after passport submission
const sendPassportNotificationSms = async (mobileNumber: string, fullName: string, passportNumber: string, uniqueCode: string) => {
  try {
    if (!mobileNumber) {
      console.warn('No mobile number provided. Skipping SMS notification.');
      return null;
    }
    
    // Format the phone number (the formatPhoneNumber method will handle country codes)
    const formattedNumber = SmsService.formatPhoneNumber(mobileNumber);
    
    // Create SMS message
    const message = `عزيزي ${fullName}, تم تثبيت اسمك في النظام بنجاح, رقم جوازك ${passportNumber},كود المكتب الخاص بك :${uniqueCode}`;
    
    // Send SMS using the static method
    const result = await SmsService.sendSms(
      formattedNumber, 
      message, 
      'passport_created', // message type
      undefined, // related_id (could be set to passport ID if available)
      fullName // recipient name
    );
    
    console.log('SMS sent successfully:', result);
    
    // Show notification for successful SMS
    $q.notify({
      type: 'positive',
      message: 'تم إرسال رسالة نصية بمعلومات المتابعة',
      position: 'top'
    });
    
    return result;
  } catch (error) {
    console.error('Failed to send passport submission SMS:', error);
    
    // Show notification for SMS failure
    $q.notify({
      type: 'warning',
      message: 'تم إضافة الجواز بنجاح ولكن فشل إرسال الرسالة النصية',
      position: 'top'
    });
    
    return null;
  }
};

const onSubmit = async (formData: FormData) => {
  loading.value = true;
  try {
    console.log('Submitting form data:', formData);

    // Check if formData has all required fields
    const requiredFields = [
      'full_name',
      'nationality',
      'passport_number',
      'date_of_birth',
      'passport_delivery_date',
      'payment_status'
    ];

    const missingFields = requiredFields.filter((field) => !formData.get(field));
    if (missingFields.length > 0) {
      throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
    }

    // Log the payment status for debugging
    console.log('Payment status value:', formData.get('payment_status'));

    // Manually build the payload for debugging
    const payload: Record<string, any> = {};
    for (const [key, value] of formData.entries()) {
      payload[key] = value;
    }
    console.log('Payload to be sent:', payload);

    // Directly call the passports store's create method
    const result = await passports.create(formData);
    console.log('Passport creation result:', result);

    // Extract data for SMS notification
    const mobileNumber = formData.get('mobile_number') as string;
    const fullName = formData.get('full_name') as string;
    const passportNumber = formData.get('passport_number') as string;
    const uniqueCode = result.unique_code as string; // Get uniqueCode from the result object
    
    console.log('SMS data before sending:', { mobileNumber, fullName, passportNumber, uniqueCode });

    // Send SMS notification
    await sendPassportNotificationSms(mobileNumber, fullName, passportNumber, uniqueCode);

    $q.notify({
      type: 'positive',
      message: 'تم إضافة الجواز بنجاح',
      position: 'top'
    });

    router.push({ name: 'PassIndex' });
  } catch (error: any) {
    console.error('Create passport error details:', {
      message: error?.message || 'Unknown error',
      response: error?.response?.data || null,
      status: error?.response?.status || 500
    });

    // Attempt to parse the response
    let errorMessage = error?.message || 'حدث خطأ أثناء إضافة الجواز';

    if (error?.response?.data) {
      if (typeof error.response.data === 'string') {
        try {
          const parsedError = JSON.parse(error.response.data);
          errorMessage = parsedError.message || errorMessage;
        } catch (parseError) {
          console.error('Error parsing response data:', parseError);
        }
      } else if (error.response.data.message) {
        errorMessage = error.response.data.message;
      }
    }

    $q.notify({
      type: 'negative',
      message: errorMessage,
      position: 'top',
      timeout: 5000
    });
  } finally {
    loading.value = false;
  }
};

const runSimpleTest = async () => {
  try {
    const result = await passports.testSimplePost();
    console.log('Simple API test result:', result);
    $q.notify({
      type: 'positive',
      message: 'API Test Successful',
      position: 'top'
    });
  } catch (error: any) {
    console.error('Simple API test error:', error);
    $q.notify({
      type: 'negative',
      message: `API Test Failed: ${error.message || 'Unknown error'}`,
      position: 'top'
    });
  }
};

// Test SMS functionality
const testSms = async () => {
  try {
    loading.value = true;
    $q.notify({
      type: 'info',
      message: 'جاري إرسال رسالة اختبارية...',
      position: 'top',
      timeout: 2000
    });

    const result = await SmsService.sendSms(
      '+98501234567', 
      'هذه رسالة اختبار من نظام Radwan',
      'test',
      undefined,
      'Test User'
    );
    
    console.log('SMS test result:', result);
    
    $q.notify({
      type: 'positive',
      message: 'تم إرسال رسالة SMS اختبارية بنجاح',
      position: 'top',
      timeout: 3000
    });
  } catch (error: any) {
    console.error('SMS test error:', error);
    
    let errorMessage = 'فشل اختبار SMS';
    
    // Provide more specific error details when available
    if (error.response) {
      errorMessage += `: ${error.response.status} - ${error.response.statusText}`;
    } else if (error.message) {
      errorMessage += `: ${error.message}`;
    } else {
      errorMessage += ': خطأ غير معروف';
    }
    
    $q.notify({
      type: 'negative',
      message: errorMessage,
      position: 'top',
      timeout: 5000
    });
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <q-page>
    <base-breadcrumbs />
    <q-card flat bordered class="q-pa-md">
      <q-card-section>
        <div class="text-h6 q-mb-md">إضافة جواز جديد</div>
        
        <PassForm :submit-loading="loading" @submit="onSubmit" />
      </q-card-section>
    </q-card>
  </q-page>
</template>
