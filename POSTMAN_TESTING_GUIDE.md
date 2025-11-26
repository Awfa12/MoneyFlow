# Postman Testing Guide for MoneyFlow API

## 📋 Prerequisites

- Postman installed
- Docker containers running (`docker-compose up -d`)
- Laravel app accessible at `http://localhost:8000`

---

## 🧪 Step-by-Step Testing

### **Test 1: Register a New User**

#### 1. Create New Request
- Click **"New"** button → Select **"HTTP Request"**
- Name it: `Register User`

#### 2. Configure the Request
- **Method**: Select `POST` from dropdown
- **URL**: `http://localhost:8000/api/register`

#### 3. Set Headers
- Click **"Headers"** tab
- Add header:
  - **Key**: `Content-Type`
  - **Value**: `application/json`

#### 4. Set Request Body
- Click **"Body"** tab
- Select **"raw"** radio button
- In the dropdown next to "raw", select **"JSON"**
- Paste this JSON:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### 5. Send Request
- Click **"Send"** button
- You should see status **201 Created**
- Response should look like:
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### ✅ Expected Result:
- Status: `201 Created`
- Response contains user data (without password)
- No errors

---

### **Test 2: Login (Get Token)**

#### 1. Create New Request
- Click **"New"** → **"HTTP Request"**
- Name it: `Login User`

#### 2. Configure the Request
- **Method**: `POST`
- **URL**: `http://localhost:8000/api/login`

#### 3. Set Headers
- Click **"Headers"** tab
- Add:
  - **Key**: `Content-Type`
  - **Value**: `application/json`

#### 4. Set Request Body
- Click **"Body"** tab
- Select **"raw"** → **"JSON"**
- Paste this JSON (use the email you registered):
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

#### 5. Send Request
- Click **"Send"**
- You should see status **200 OK**
- Response should look like:
```json
{
    "message": "Login successful",
    "token": "1|abcdef1234567890abcdef1234567890...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

#### ✅ Expected Result:
- Status: `200 OK`
- Response contains a **token** (long string starting with numbers)
- **⚠️ IMPORTANT**: Copy this token - you'll need it for authenticated requests!

#### 🔑 Save the Token:
- Copy the entire token value (e.g., `1|abcdef1234567890...`)
- Keep it somewhere safe - you'll use it in the Authorization header

---

### **Test 3: Test Invalid Credentials**

#### 1. Use the Login Request
- Edit the existing `Login User` request

#### 2. Change the Body
```json
{
    "email": "john@example.com",
    "password": "wrongpassword"
}
```

#### 3. Send Request
- Click **"Send"**
- You should see status **422 Unprocessable Entity**
- Response should look like:
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

#### ✅ Expected Result:
- Status: `422` (Validation Error)
- Clear error message about incorrect credentials

---

### **Test 4: Test Register Validation Errors**

#### 1. Use the Register Request
- Edit the existing `Register User` request

#### 2. Try Missing Fields
Change body to:
```json
{
    "name": "John Doe"
}
```

#### 3. Send Request
- You should see status **422**
- Response shows validation errors:
```json
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

#### 4. Try Duplicate Email
Change body to:
```json
{
    "name": "Jane Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### 5. Send Request
- You should see status **422**
- Error about email already being taken

---

## 📝 Common Issues & Solutions

### Issue: "Connection refused" or "Could not get response"
**Solution**: 
- Check if Docker containers are running: `docker-compose ps`
- Make sure containers show "Up" status
- Restart if needed: `docker-compose restart nginx`

### Issue: "404 Not Found"
**Solution**:
- Verify URL is correct: `http://localhost:8000/api/register`
- Check that `routes/api.php` exists and has the routes
- Clear route cache: `docker-compose exec app php artisan route:clear`

### Issue: "500 Internal Server Error"
**Solution**:
- Check Laravel logs: `docker-compose exec app tail -f storage/logs/laravel.log`
- Make sure database is connected and migrations ran
- Check `.env` file has correct database credentials

### Issue: Token not working in future requests
**Solution**:
- Make sure you're using the full token (starts with number and pipe: `1|...`)
- Include `Bearer ` prefix in Authorization header: `Bearer 1|abc123...`
- Token expires when user deletes it or server restarts (in development)

---

## 🎯 Quick Reference

### Register Endpoint
- **URL**: `POST http://localhost:8000/api/register`
- **Body**: 
  ```json
  {
    "name": "string",
    "email": "email",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Success**: `201 Created`

### Login Endpoint
- **URL**: `POST http://localhost:8000/api/login`
- **Body**:
  ```json
  {
    "email": "email",
    "password": "string"
  }
  ```
- **Success**: `200 OK` (returns token)

---

## 📚 Next Steps

Once you've successfully tested:
1. ✅ Register creates a user
2. ✅ Login returns a token
3. ✅ Invalid credentials show errors

You're ready for **Day 2: Wallet Creation with Observers**!

---

## 💡 Pro Tips

1. **Create a Postman Collection**: 
   - Click "New" → "Collection"
   - Name it "MoneyFlow API"
   - Drag your requests into the collection for organization

2. **Save Environment Variables**:
   - Create environment with variable `base_url = http://localhost:8000`
   - Use `{{base_url}}/api/register` in URLs
   - Easy to switch between local/production

3. **Save Token Automatically**:
   - In Login request, go to "Tests" tab
   - Add script: `pm.environment.set("token", pm.response.json().token);`
   - Use `{{token}}` in Authorization header for future requests

4. **Test Error Cases**:
   - Missing fields
   - Invalid email format
   - Password too short
   - Duplicate email
   - Wrong credentials

