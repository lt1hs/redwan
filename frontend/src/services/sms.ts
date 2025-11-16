import { api } from '@/utils/axios'; // Make sure this 'api' instance is configured to point to your Laravel backend's base URL (e.g., http://localhost:8000/api)
// import axios from 'axios'; // No longer need direct axios import if using the 'api' instance

export interface SmsLog {
  id: number;
  recipient: string;
  message: string;
  status: 'SENT' | 'PENDING' | 'FAILED';
  error?: string;
  created_at: string;
  updated_at?: string;
  type?: string;
  recipient_name?: string;
  related_id?: number;
  response_data?: string;
  retries?: number;
}

export interface SmsCredit {
  balance: number;
  currency: string;
  expiry_date?: string;
}

export class SmsService {
  private static instance: SmsService;
  private useLocalMock: boolean = false; // Set to false to use real API

  // --- REMOVE THESE DIRECT API CONFIGURATIONS ---
  // private MELIPAYAMAK_API_URL = 'https://rest.payamak-panel.com/api/SendSMS/SendSMS'.trim();
  // private MELIPAYAMAK_USERNAME = '9011771008';
  // private MELIPAYAMAK_PASSWORD = '6C0$D';
  // private MELIPAYAMAK_SENDER = '5000xxx';
  // --- END REMOVAL ---

  private constructor() {
    // Private constructor for singleton pattern
    // --- REMOVE localStorage loading of credentials from frontend ---
    // this.loadCredentials();
  }

  // --- REMOVE loadCredentials method as credentials are now backend-only ---
  // private loadCredentials(): void { /* ... */ }

  // For singleton pattern
  public static getInstance(): SmsService {
    if (!SmsService.instance) {
      SmsService.instance = new SmsService();
    }
    return SmsService.instance;
  }

  // Format phone numbers consistently
  // Helper function to convert Arabic/Persian numerals to Latin numerals
  private static convertArabicToLatinNumerals(input: string): string {
    return input
      .replace(/[\u0660-\u0669]/g, (c) => (c.charCodeAt(0) - 0x0660).toString()) // Arabic numerals
      .replace(/[\u06F0-\u06F9]/g, (c) => (c.charCodeAt(0) - 0x06F0).toString()); // Persian numerals
  }

  public static formatPhoneNumber(phone: string): string {
    if (!phone) return '';

    // First, convert any Arabic/Persian numerals to Latin numerals
    let cleaned = SmsService.convertArabicToLatinNumerals(phone);

    // Then, remove any non-digit characters (now that all digits are Latin)
    cleaned = cleaned.replace(/\D/g, '');

    // If it starts with '+', remove it
    if (phone.startsWith('+')) {
      cleaned = cleaned.substring(1);
    }

    // Handle Iranian numbers (MeliPayamak is an Iranian provider)
    if (!cleaned.startsWith('98')) {
      // Remove leading zero if present (e.g., 0912... -> 912...)
      if (cleaned.startsWith('0')) {
        cleaned = cleaned.substring(1);
      }
      // Add Iranian country code (98)
      cleaned = '98' + cleaned;
    }

    return cleaned;
  }

  // Main SMS sending function - NOW CALLS YOUR LARAVEL BACKEND
  public async sendSms(
    recipient: string,
    message: string,
    type: string = 'general',
    related_id?: number,
    recipient_name?: string
  ): Promise<SmsLog> {
    console.log(`Attempting to send SMS via Laravel backend to ${recipient}: ${message}`);

    if (this.useLocalMock) {
      return this.mockSendSms(recipient, message, type);
    }

    try {
      // Format the recipient number
      const formattedRecipient = SmsService.formatPhoneNumber(recipient);
      console.log('Formatted recipient number before API call:', formattedRecipient);

      // --- CRITICAL CHANGE: Call your Laravel backend API endpoint ---
      const response = await api.post('/admin/sms/send', {
        to: formattedRecipient,
        text: message,
        type: type, // Pass type to backend if it needs to log it
        related_id: related_id, // Pass related_id to backend
        recipient_name: recipient_name // Pass recipient_name to backend
      });

      console.log('Laravel backend SMS response:', response.data);

      // Assuming your Laravel backend returns a success/failure status and potentially the created log data
      // You'll need to adapt this based on what your Laravel controller actually returns.
      // For now, let's assume a basic success status from Laravel.
      const smsLog: SmsLog = {
        id: response.data.data?.id || Date.now(), // Get ID from backend if available, otherwise mock
        recipient: formattedRecipient,
        message,
        status: response.data.message === 'SMS sent successfully' ? 'SENT' : 'FAILED', // Or check a more robust status field from backend
        created_at: response.data.data?.created_at || new Date().toISOString(),
        type,
        response_data: JSON.stringify(response.data),
        error: response.data.details || (response.data.message !== 'SMS sent successfully' ? response.data.message : undefined)
      };

      return smsLog;
    } catch (error) {
      console.error('Error sending SMS via Laravel backend:', error);

      const fallbackLog: SmsLog = {
        id: Math.floor(Math.random() * 10000),
        recipient,
        message,
        status: 'FAILED',
        error: error instanceof Error ? error.message : String(error),
        created_at: new Date().toISOString(),
        type
      };

      throw error;
    }
  }

  // Mock implementation for local testing (can remain as is for frontend mocks)
  private mockSendSms(recipient: string, message: string, type: string): Promise<SmsLog> {
    // Placeholder mock logic to satisfy TypeScript
    return Promise.resolve({
      id: Date.now(),
      recipient,
      message,
      status: 'SENT',
      created_at: new Date().toISOString(),
      type
    });
  }

  // Check remaining SMS credit - NOW CALLS YOUR LARAVEL BACKEND
  public async checkCredit(): Promise<number> {
    if (this.useLocalMock) {
      return this.mockCheckCredit();
    }

    try {
      // --- CRITICAL CHANGE: Call your Laravel backend API endpoint ---
      const response = await api.get('/admin/sms/credit');

      console.log('Laravel backend credit response:', response.data);

      // Adapt this based on what your Laravel controller returns for credit.
      // Your Laravel backend should call Melipayamak's GetCredit and return its value.
      return Number(response.data.balance) || 0; // Assuming Laravel returns { balance: number }
    } catch (error) {
      console.error('Error checking SMS credit via Laravel backend:', error);
      return 0;
    }
  }

  // Mock credit check (can remain as is)
  private mockCheckCredit(): Promise<number> {
    // Placeholder mock logic to satisfy TypeScript
    return Promise.resolve(1000);
  }

  // Send SMS to multiple recipients - NOW CALLS YOUR LARAVEL BACKEND
  public async sendBulkSms(recipients: string[], message: string, type: string = 'general'): Promise<SmsLog[]> {
    if (this.useLocalMock) {
      return this.mockSendBulkSms(recipients, message, type);
    }

    try {
      const formattedRecipients = recipients.map(r => SmsService.formatPhoneNumber(r));

      // --- CRITICAL CHANGE: Call your Laravel backend API endpoint ---
      const response = await api.post('/admin/sms/send-bulk', { // You might need a specific bulk send route in Laravel
        to: formattedRecipients, // Laravel's Http facade can handle arrays for 'to'
        text: message,
        type: type
      });

      console.log('Laravel backend bulk send response:', response.data);

      // Adapt parsing based on Laravel's response for bulk send
      const status: 'SENT' | 'FAILED' = response.data.message === 'Bulk SMS sent successfully' ? 'SENT' : 'FAILED';
      return formattedRecipients.map(recipient => ({
        id: Date.now() + Math.floor(Math.random() * 1000),
        recipient,
        message,
        status,
        created_at: new Date().toISOString(),
        type,
        response_data: JSON.stringify(response.data),
        error: response.data.details || (response.data.message !== 'Bulk SMS sent successfully' ? response.data.message : undefined)
      }));
    } catch (error) {
      console.error('Error sending bulk SMS via Laravel backend:', error);

      return recipients.map(recipient => ({
        id: Math.floor(Math.random() * 10000),
        recipient,
        message,
        status: 'FAILED' as const,
        error: error instanceof Error ? error.message : String(error),
        created_at: new Date().toISOString(),
        type
      }));
    }
  }

  // Mock bulk send (can remain as is)
  private mockSendBulkSms(recipients: string[], message: string, type: string): Promise<SmsLog[]> {
    // Placeholder mock logic to satisfy TypeScript
    return Promise.resolve(recipients.map(r => ({
      id: Date.now() + Math.random(),
      recipient: r,
      message,
      status: 'SENT',
      created_at: new Date().toISOString(),
      type
    })));
  }


  // Delivery status check - NOW CALLS YOUR LARAVEL BACKEND
  public async checkMessageStatus(messageId: string): Promise<any> {
    if (this.useLocalMock) {
      return this.mockCheckMessageStatus(messageId);
    }

    try {
      // --- CRITICAL CHANGE: Call your Laravel backend API endpoint ---
      const response = await api.get(`/admin/sms/status/${messageId}`);

      return response.data;
    } catch (error) {
      console.error('Error checking message status via Laravel backend:', error);
      throw error;
    }
  }

  // Mock status check (can remain as is)
  private mockCheckMessageStatus(messageId: string): Promise<any> {
    // Placeholder mock logic to satisfy TypeScript
    return Promise.resolve({ status: 'DELIVERED' });
  }

  // Static method for one-off sends
  public static async sendSms(
    recipient: string,
    message: string,
    type: string = 'general',
    related_id?: number,
    recipient_name?: string
  ): Promise<SmsLog> {
    return SmsService.getInstance().sendSms(recipient, message, type, related_id, recipient_name);
  }

  // Static method to check credit
  public static async getCredit(): Promise<SmsCredit> {
    const credit = await SmsService.getInstance().checkCredit();
    return {
      balance: credit,
      currency: 'IRR' // Iranian Rial for Melipayamak
    };
  }

  // --- REMOVE frontend-based credential setting and storage ---
  // public setCredentials(username: string, password: string, sender: string): void { /* ... */ }
  // public static setCredentials(username: string, password: string, sender: string): void { /* ... */ }
  // public static getCredentials(): { username: string; password: string; sender: string } { /* ... */ }

  // Enable or disable mock mode
  public static setMockMode(useMock: boolean): void {
    (SmsService.getInstance() as any).useLocalMock = useMock;
  }
}

// Export as default as well
export default SmsService;
