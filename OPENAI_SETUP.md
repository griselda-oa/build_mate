# OpenAI API Key Setup Guide

This guide will help you get your OpenAI API key and configure it for sentiment analysis.

## Step 1: Get Your OpenAI API Key

### Option A: Sign Up for OpenAI (If you don't have an account)

1. Go to [https://platform.openai.com](https://platform.openai.com)
2. Click **"Sign Up"** or **"Log In"**
3. Create an account (you can use Google/Microsoft account)
4. Verify your email address

### Option B: Get API Key (If you already have an account)

1. Log in to [https://platform.openai.com](https://platform.openai.com)
2. Click on your profile icon (top right)
3. Select **"API Keys"** from the dropdown
4. Click **"Create new secret key"**
5. Give it a name (e.g., "Build Mate Sentiment Analysis")
6. **Copy the key immediately** - you won't be able to see it again!
   - The key looks like: `sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

### Important Notes:
- ⚠️ **Keep your API key secret!** Never share it publicly or commit it to version control
- 💰 OpenAI charges per API call (very affordable for sentiment analysis)
- 📊 Check usage at: [https://platform.openai.com/usage](https://platform.openai.com/usage)
- 💵 You may need to add payment method for API access (they have free credits for new users)

## Step 2: Configure the API Key

You have **two options** to add your API key:

### Option 1: Using .env File (Recommended)

1. Check if you have a `.env` file in your project root:
   ```bash
   ls -la .env
   ```

2. If `.env` doesn't exist, create it:
   ```bash
   touch .env
   ```

3. Open `.env` in a text editor and add:
   ```env
   OPENAI_API_KEY=sk-proj-your-actual-api-key-here
   ```

4. Save the file

### Option 2: Direct Configuration (Alternative)

If you prefer not to use `.env`, you can add it directly to `settings/config.php`:

1. Open `settings/config.php`
2. Find the `'ai'` section (around line 67)
3. Change:
   ```php
   'openai_api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
   ```
   To:
   ```php
   'openai_api_key' => $_ENV['OPENAI_API_KEY'] ?? 'sk-proj-your-actual-api-key-here',
   ```

## Step 3: Verify It's Working

1. Submit a test review on any product
2. The sentiment should be automatically analyzed
3. Check the review - you should see a sentiment badge (Positive/Neutral/Negative)

## Troubleshooting

### Error: "OpenAI API key not configured"
- Make sure you added the key to `.env` or `config.php`
- Restart your web server after adding the key
- Check that the key starts with `sk-`

### Error: "OpenAI API error (401)"
- Your API key is invalid or expired
- Generate a new key from OpenAI dashboard
- Make sure you copied the entire key (it's very long)

### Error: "OpenAI API error (429)"
- You've hit the rate limit
- Wait a few minutes and try again
- Check your usage limits at OpenAI dashboard

### Sentiment Analysis Not Working?
- The system will fall back to rating-based sentiment if AI fails
- Check your server error logs for details
- Make sure your OpenAI account has credits/balance

## Cost Information

- **GPT-3.5-turbo** (used for sentiment analysis): ~$0.0015 per 1K tokens
- **Average review analysis**: ~100-200 tokens per review
- **Cost per review**: ~$0.0002 (less than 1 cent!)
- **1000 reviews**: ~$0.20

OpenAI provides free credits for new accounts, so you can test without cost.

## Security Best Practices

1. ✅ **DO**: Add `.env` to `.gitignore` (if using Git)
2. ✅ **DO**: Use environment variables for API keys
3. ❌ **DON'T**: Commit API keys to version control
4. ❌ **DON'T**: Share API keys in screenshots or messages
5. ❌ **DON'T**: Use the same key for multiple projects

## Need Help?

- OpenAI Documentation: [https://platform.openai.com/docs](https://platform.openai.com/docs)
- OpenAI Support: [https://help.openai.com](https://help.openai.com)
- Check your API usage: [https://platform.openai.com/usage](https://platform.openai.com/usage)

