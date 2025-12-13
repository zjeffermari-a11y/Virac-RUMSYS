# Backblaze B2 Storage Setup for Profile Pictures

This application uses Backblaze B2 for storing profile pictures instead of local storage. This reduces server storage usage and provides better scalability.

## Prerequisites

1. A Backblaze B2 account (sign up at https://www.backblaze.com/b2/sign-up.html)
2. A B2 bucket created in your Backblaze account
3. Application Key ID and Application Key from Backblaze

## Setup Instructions

### 1. Create a B2 Bucket

1. Log in to your Backblaze account
2. Go to "Buckets" section
3. Click "Create a Bucket"
4. Choose a bucket name (e.g., `virac-rumsys-profile-pictures`)
5. Set the bucket to **Public** (so profile pictures can be accessed via URL)
6. Note the bucket name and region

### 2. Create Application Keys

1. Go to "App Keys" section in Backblaze
2. Click "Add a New Application Key"
3. Give it a name (e.g., "Laravel App")
4. Select the bucket you created
5. Set permissions to "Read and Write"
6. Copy the **Key ID** and **Application Key** (you'll only see the Application Key once!)

### 3. Get Your B2 Endpoint URL

1. In your bucket settings, find the "Endpoint" or "Download URL"
2. It should look like: `https://f005.backblazeb2.com`
3. Note this URL

### 4. Configure Environment Variables

Add the following to your `.env` file:

```env
# Backblaze B2 Configuration
B2_KEY_ID=your_key_id_here
B2_APPLICATION_KEY=your_application_key_here
B2_BUCKET_NAME=your-bucket-name
B2_REGION=us-east-005
B2_ENDPOINT=https://f005.backblazeb2.com
B2_URL=https://f005.backblazeb2.com/file/your-bucket-name
```

**Important Notes:**
- Replace `your_key_id_here` with your actual Key ID
- Replace `your_application_key_here` with your actual Application Key
- Replace `your-bucket-name` with your actual bucket name
- Replace the endpoint URL with your actual B2 endpoint
- The `B2_URL` should be the public download URL for your bucket

### 5. Verify Configuration

After setting up, test the configuration by:
1. Uploading a profile picture through the application
2. Checking the logs to see if the upload was successful
3. Verifying the image displays correctly

## Troubleshooting

### Images Not Displaying

1. **Check bucket visibility**: Ensure the bucket is set to "Public" in Backblaze
2. **Verify B2_URL**: Make sure `B2_URL` in `.env` matches your bucket's public URL
3. **Check file permissions**: Ensure the Application Key has "Read" permissions
4. **Check logs**: Look for errors in `storage/logs/laravel.log`

### Upload Failures

1. **Verify credentials**: Double-check `B2_KEY_ID` and `B2_APPLICATION_KEY`
2. **Check bucket name**: Ensure `B2_BUCKET_NAME` matches exactly
3. **Verify endpoint**: Ensure `B2_ENDPOINT` is correct for your region
4. **Check file size**: Profile pictures are limited to 2MB

### Common Errors

- **"Access Denied"**: Check Application Key permissions
- **"Bucket Not Found"**: Verify bucket name spelling
- **"Invalid Endpoint"**: Check region and endpoint URL

## Migration from Local Storage

If you have existing profile pictures in local storage:
1. They will continue to work (the User model accessor handles both)
2. New uploads will go to B2
3. Old images can be migrated manually if needed

## Cost Considerations

Backblaze B2 pricing (as of 2024):
- **Storage**: $0.005/GB/month (first 10GB free)
- **Download**: $0.01/GB (first 1GB free per day)
- **Upload**: Free

For a typical application with profile pictures:
- Storage costs are minimal (pictures are small)
- Download costs depend on traffic
- Most small applications stay within free tiers

## Security Notes

- Application Keys should be kept secret (never commit to git)
- Use environment variables for all B2 credentials
- Consider using different keys for different environments (dev/staging/production)
- Regularly rotate Application Keys for security
