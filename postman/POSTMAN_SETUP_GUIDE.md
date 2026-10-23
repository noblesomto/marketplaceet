# Postman Collection Setup Guide

## 📥 Installation & Setup

### Step 1: Import Files into Postman

1. **Open Postman** (Download from https://www.postman.com if needed)

2. **Import Collection:**
   - Click "Import" button (top left)
   - Drag and drop `Marketplace-API-Complete.postman_collection.json`
   - Or click "Upload Files" and select the file
   - Click "Import"

3. **Import Environments:**
   - Click "Import" again
   - Select both environment files from `environments/` folder:
     - `Local.postman_environment.json`
     - `Production.postman_environment.json`
   - Click "Import"

### Step 2: Select Environment

1. Click the **environment dropdown** (top right, next to the eye icon)
2. Select either:
   - **"Marketplace Ethiopia - Local"** for development
   - **"Marketplace Ethiopia - Production"** for live API

### Step 3: First Request (Login)

1. In Collections panel, expand **"Marketplace Ethiopia - Complete API"**
2. Open folder **"1. Authentication"**
3. Click on **"Login"** request
4. In the **Body** tab, update:
   ```json
   {
     "email": "your-test-email@example.com",
     "password": "your-password"
   }
   ```
5. Click **"Send"**

6. ✅ If successful, you'll see:
   - Status: `200 OK`
   - Response with `access_token` and `user` object
   - In Tests tab: All tests passing ✓
   - **Token automatically saved to environment!**

### Step 4: Test Protected Endpoint

1. Navigate to any protected endpoint (e.g., "2. Adverts > Get My Adverts")
2. Click **"Send"** - it should work automatically!
3. No need to copy/paste tokens - collection handles it!

---

## 🔑 Authentication System

### How It Works

The collection uses **automatic token management**:

1. **On Login Success:**
   ```javascript
   // This script runs automatically:
   const jsonData = pm.response.json();
   pm.environment.set("access_token", jsonData.access_token);
   pm.environment.set("user_id", jsonData.user.user_id);
   ```

2. **On Every Protected Request:**
   ```
   Headers automatically include:
   Authorization: Bearer {{access_token}}
   ```

3. **Token Expired?**
   - Just run Login again
   - Token auto-updates everywhere
   - No manual updates needed!

---

## 🌐 Environment Configuration

### Local Environment

**When to use:** Testing against local development server

**Base URL:** `http://127.0.0.1:8030` (Update port if different)

**Setup:**
1. Ensure local server is running: `php artisan serve --port=8030`
2. Select "Marketplace Ethiopia - Local" environment
3. Run Login request
4. Start testing!

### Production Environment

**When to use:** Testing against live server

**Base URL:** `https://www.marketplace.et`

**Setup:**
1. Select "Marketplace Ethiopia - Production" environment
2. Use real account credentials
3. Run Login request
4. Start testing!

### Switching Environments

Simply select different environment from dropdown - all requests automatically use the new base URL!

---

## 📝 Environment Variables Reference

### Auto-Managed (Set by Scripts)

| Variable | Set By | Example Value |
|----------|--------|---------------|
| `access_token` | Login/Register | `2\|AbC123XyZ...` |
| `user_id` | Login/Register | `USR12345` |
| `user_email` | Login | `user@example.com` |
| `advert_id` | View Advert | `123` |
| `seller_id` | View Advert | `USR67890` |

### Manual (You Set)

| Variable | Purpose | Example |
|----------|---------|---------|
| `base_url` | API endpoint | `http://127.0.0.1:8030` |
| `category_id` | Filter by category | `1` |
| `subcategory_id` | Filter by subcategory | `6` |
| `brand_id` | Filter by brand | `15` |

### How to Set Manual Variables

1. Click environment name (top right)
2. Click "Edit"
3. Update "CURRENT VALUE" column
4. Click "Save"

---

## ✅ Using Test Scripts

### What Are They?

Every request has built-in tests that:
- ✓ Validate HTTP status codes
- ✓ Check response structure
- ✓ Verify required fields exist
- ✓ Auto-extract and save values

### Viewing Test Results

After sending a request:
1. Click **"Test Results"** tab (bottom)
2. See which tests passed/failed
3. Green ✓ = Passed
4. Red ✗ = Failed (with error message)

### Example Test Output

```
✓ Status code is 200
✓ Response has access_token
✓ Response has user object
✓ Access token saved to environment
```

---

## 📤 File Upload Requests

Some endpoints accept file uploads (images, documents).

### How to Upload Files

1. Select request (e.g., "Create Advert")
2. Go to **"Body"** tab
3. Select **"form-data"** (not raw JSON)
4. Add fields:

   | KEY | VALUE | TYPE |
   |-----|-------|------|
   | ad_title | iPhone 13 | Text |
   | price | 450000 | Text |
   | images[] | [Select File] | File |
   | images[] | [Select File] | File |

5. Click **"Send"**

### Multi-File Upload

For multiple images:
- Use same key name: `images[]`
- Add multiple rows
- Each row = one file

---

## 🔍 Search & Filter Examples

### Basic Search

```
GET /api/search?q=iphone&state=addis-ababa
```

Variables in URL:
- `{{base_url}}/api/search?q=iphone&state=addis-ababa`

### Advanced Filter

```json
POST /api/search/filter
{
  "category": 1,
  "subcategory": 6,
  "min_price": 100000,
  "max_price": 500000,
  "state": "addis-ababa",
  "condition": "new",
  "brand": 15
}
```

---

## 🐛 Troubleshooting Guide

### Issue: 401 Unauthorized Error

**Cause:** Token missing or expired

**Solution:**
1. Run **Login** request again
2. Check **Test Results** tab - token should auto-save
3. Verify environment shows `access_token` value
4. Retry failed request

### Issue: 422 Validation Error

**Cause:** Request data invalid

**Solution:**
1. Check response **Body** for error messages:
   ```json
   {
     "errors": {
       "price": ["The price field is required"],
       "category": ["The category field is required"]
     }
   }
   ```
2. Update request body with missing/correct fields
3. Retry

### Issue: 404 Not Found

**Cause:** Resource doesn't exist or wrong ID

**Solution:**
1. Verify the ID you're using exists
2. Check environment variable: `{{advert_id}}`, `{{user_id}}`, etc.
3. Run a "Get All" request first to find valid IDs
4. Update environment variable with correct ID

### Issue: 500 Internal Server Error

**Cause:** Server-side problem

**Solution:**
1. Verify you're using correct environment (Local vs Production)
2. Check if server is running (for Local)
3. Review request format - ensure matches documentation
4. Check server logs if accessible
5. Contact backend team

### Issue: Collection Not Showing

**Cause:** Import failed or wrong file

**Solution:**
1. Re-import collection file
2. Ensure file is `.postman_collection.json`
3. Check file isn't corrupted
4. Try drag-and-drop import method

### Issue: Variables Not Auto-Saving

**Cause:** Test scripts not running

**Solution:**
1. Check **Test Results** tab after request
2. Look for console logs (View > Show Postman Console)
3. Verify Scripts tab has test code
4. Re-import collection if tests missing

---

## 🔄 Collection Runner (Automated Testing)

### Run All Requests Automatically

1. Right-click collection name
2. Select **"Run collection"**
3. Configure:
   - Select environment
   - Choose folder or all requests
   - Set iterations (how many times to run)
4. Click **"Run Marketplace Ethiopia - Complete API"**
5. Watch automated test execution!

### View Results

- See all requests execute in order
- Pass/fail status for each
- Total time taken
- Export results as JSON/HTML

---

## 📊 Monitoring & Reporting

### Export Test Results

1. Run Collection Runner
2. After completion, click **"Export Results"**
3. Choose format (JSON or HTML)
4. Save file for documentation/reporting

### Share Collection with Team

**Method 1: Export/Import**
1. Right-click collection
2. Select "Export"
3. Share JSON file with team
4. They import same file

**Method 2: Postman Workspace**
1. Create Team Workspace (requires Postman account)
2. Invite team members
3. Everyone syncs automatically

---

## 🎯 Best Practices

### For Developers

1. **Always test in Local first**
   - Don't test destructive operations in Production
   - Use Local environment for development

2. **Use environment variables**
   - Never hardcode IDs
   - Always use `{{advert_id}}` instead of `123`

3. **Check test results**
   - Don't just look at response
   - Verify tests passed

4. **Organize requests**
   - Use folders to group related endpoints
   - Name requests clearly

5. **Document findings**
   - Add notes to requests
   - Export results for bug reports

### For QA/Testing

1. **Run full test suite** regularly
   - Use Collection Runner
   - Test both environments

2. **Validate error cases**
   - Try invalid data
   - Test edge cases
   - Verify error messages

3. **Check performance**
   - Note response times
   - Identify slow endpoints

4. **Document bugs**
   - Export failed test results
   - Include request/response details
   - Note environment used

---

## 🚀 Advanced Usage

### Pre-Request Scripts

Add code that runs BEFORE request:

```javascript
// Generate random email
pm.environment.set("test_email",
  `test${Date.now()}@example.com`
);

// Set current timestamp
pm.environment.set("timestamp", new Date().toISOString());
```

### Custom Variables

Create your own environment variables:
1. Click Environment name
2. Click "Add" under variables
3. Set name and value
4. Use with `{{variable_name}}`

### Request Chaining

Requests can use data from previous requests:

```javascript
// In Request A test script:
pm.environment.set("created_advert_id", jsonData.advert.id);

// In Request B URL:
{{base_url}}/api/adverts/{{created_advert_id}}
```

---

## 📱 Mobile App Integration Tips

### Use Collection as Reference

The Postman collection shows exactly what mobile app should send:

1. **Check request structure**
   - Headers needed
   - Body format
   - Required fields

2. **Copy request examples**
   - Use same JSON structure
   - Same field names
   - Same data types

3. **Test error handling**
   - See what errors API returns
   - Plan for error states in app

### Token Management in App

Based on Postman flow, implement:

```javascript
// After successful login API call:
const {access_token, user} = response.data;

// Save to secure storage
await SecureStore.setItemAsync('access_token', access_token);
await AsyncStorage.setItem('user', JSON.stringify(user));

// Use in all API calls
const token = await SecureStore.getItemAsync('access_token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
```

### Handle Token Expiry

```javascript
axios.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      // Token expired - redirect to login
      await clearAuthData();
      navigation.navigate('Login');
    }
    return Promise.reject(error);
  }
);
```

---

## 📖 Additional Resources

### Postman Documentation
- Getting Started: https://learning.postman.com
- Environments: https://learning.postman.com/docs/sending-requests/managing-environments
- Tests: https://learning.postman.com/docs/writing-scripts/test-scripts

### API Documentation
- Boost System: See `BOOST_SYSTEM_DOCUMENTATION.md`
- Boost Pages Update: See `BOOST_PAGES_UPDATE_SUMMARY.md`

### Support
- Check README.md in this folder
- Review test results for hints
- Contact backend team with:
  - Environment used
  - Request details (URL, body)
  - Full error response

---

## ✨ Quick Tips

💡 **Tip 1:** Use Postman Console (View > Show Postman Console) to see detailed request/response logs

💡 **Tip 2:** Save interesting requests as "Examples" for quick reference

💡 **Tip 3:** Use Collection Description to document API changes

💡 **Tip 4:** Export collection regularly as backup

💡 **Tip 5:** Use folders to organize requests by feature/module

---

## 🎉 You're All Set!

You now have:
- ✅ Complete API collection
- ✅ Both environments configured
- ✅ Automatic authentication
- ✅ Built-in testing
- ✅ Documentation

**Start testing and building! 🚀**

---

*Last Updated: January 2026*
*Marketplace Ethiopia*
*Postman Collection v1.0*
