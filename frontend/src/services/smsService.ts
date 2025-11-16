import axios from 'axios';

interface SMSData {
  phoneNumber: string;
  message: string;
}

export const smsService = {
  async sendSMS(data: SMSData) {
    try {
      // Replace with your actual SMS API endpoint
      const response = await axios.post('/api/sms/send', data);
      return response.data;
    } catch (error) {
      console.error('Error sending SMS:', error);
      throw error;
    }
  },

  generatePassportMessage(userName: string, passportNumber: string, userCode: string): string {
    return `عزيزي ${userName} - تم تسجيل اسمك ومعلوماتك بنجاح في النظام - رقم جواز سفرك: ${passportNumber} - رمزك: ${userCode}`;
  }
};
