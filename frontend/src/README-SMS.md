# SMS Integration Documentation

This document provides instructions for setting up and using the SMS functionality in the Radwan system Dashboard application, which uses MeliPayamak as the SMS service provider.

## Setup Instructions

### Environment Variables

Set the following environment variables in your `.env` file:

```
MELIPAYAMAK_USERNAME=your_username
MELIPAYAMAK_PASSWORD=your_password
MELIPAYAMAK_API_KEY=your_api_key
MELIPAYAMAK_SENDER=your_sender_number
```

### Backend Setup

1. Make sure the `SmsController.php` is in your Laravel application's controllers directory (`app/Http/Controllers/Api/Admin/`).
2. Run the migration to create the SMS logs table:

```bash
php artisan migrate
```

3. Set up the API routes by including the routes from `api_routes.php` in your `routes/api.php` file.

## Frontend Components

### Components

- **SmsService**: Core service handling SMS sending and management.
- **PassportSmsNotification**: Component for sending SMS notifications for passport-related operations.
- **SmsTemplateManager**: Component for managing SMS templates.
- **SmsLogsPage**: Page for viewing and managing SMS logs.
- **SmsDashboardPage**: Dashboard for SMS-related operations.

### Integration Points

The SMS functionality is integrated with the application at several points:

1. **Passport Creation**: When a new passport is created, an SMS notification is sent to the applicant.
2. **Passport Status Updates**: When a passport's status changes, notifications can be sent.
3. **Manual SMS Sending**: Available through the SMS Dashboard.

## Usage

### Sending an SMS

```typescript
import { SmsService } from '@/services/sms';

// Format the phone number
const formattedNumber = SmsService.formatPhoneNumber('0501234567');

// Send the SMS
try {
  const result = await SmsService.sendSms(
    formattedNumber, 
    'Your message here', 
    'message_type', 
    related_id, // Optional ID of related record
    'Recipient Name' // Optional recipient name
  );
  console.log('SMS sent successfully:', result);
} catch (error) {
  console.error('Failed to send SMS:', error);
}
```

### Checking SMS Credit

```typescript
import { SmsService } from '@/services/sms';

try {
  const credit = await SmsService.getCredit();
  console.log('SMS Credit:', credit);
} catch (error) {
  console.error('Failed to check SMS credit:', error);
}
```

### Viewing SMS Logs

Navigate to the SMS Logs page in the dashboard to view all SMS logs. You can filter by status, retry failed messages, and view detailed information.

## Troubleshooting

- **SMS Not Sending**: Check your MeliPayamak credentials and ensure the sender number is active.
- **Credit Check Failing**: Verify your MeliPayamak username and password.
- **Phone Number Format Issues**: The `formatPhoneNumber` method should handle most cases, but you may need to adjust it for specific country codes.

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/admin/sms/send` | POST | Send an SMS |
| `/api/admin/sms/logs` | GET | Get SMS logs with pagination |
| `/api/admin/sms/logs/recent` | GET | Get recent SMS logs |
| `/api/admin/sms/retry/{id}` | POST | Retry sending a failed SMS |
| `/api/admin/sms/logs/{id}` | DELETE | Delete an SMS log |
| `/api/admin/sms/statistics` | GET | Get SMS statistics |
| `/api/admin/sms/credit` | GET | Check SMS credit | 