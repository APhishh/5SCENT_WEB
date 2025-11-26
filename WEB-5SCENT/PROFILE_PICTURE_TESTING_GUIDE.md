# Profile Picture System - Quick Testing Guide

## Implementation Complete ✨

All requirements have been successfully implemented. Here's what to test:

---

## Test 1: Upload Profile Picture with New File Naming

**Steps:**
1. Navigate to `/profile`
2. Click "Change Photo" button
3. Select a JPG or PNG image
4. Adjust crop in the modal
5. Click save

**Expected Results:**
- ✅ File saved to `frontend/web-5scent/public/profile_pics/`
- ✅ Filename format: `{user_id}_{HHMMDDMMYYYY}.{ext}`
  - Example: `17_213526112025.png` (not `john_doe_213526112025.png`)
- ✅ Database stores only filename (check via Laravel:
  - `php artisan tinker`
  - `User::find(17)->profile_pic` should show `"17_213526112025.png"` NOT a path
- ✅ Profile picture displays immediately on profile page
- ✅ Profile picture updates in Navigation avatar (top-right corner) **without page refresh**

---

## Test 2: Cross-Machine Compatibility

**Scenario:** Access profile from different machine

**How to verify:**
1. Upload picture on Machine A (user_id=17)
  - See: `17_213526112025.png` saved
2. Access same profile from Machine B
  - Picture should display correctly
  - URL constructed as: `/profile_pics/17_213526112025.png`
  - NOT as: `http://localhost:8000/...` or absolute path

**Expected:** Picture works on all machines ✅

---

## Test 3: Remove Photo Button

**Steps:**
1. Navigate to profile page (user with profile picture)
2. Scroll to profile card
3. Verify "Remove Photo" button appears (red button below "Change Photo")
4. Click "Remove Photo"
5. Confirm action if prompted

**Expected Results:**
- ✅ Photo deleted from filesystem
- ✅ Database profile_pic set to NULL
- ✅ Letter avatar appears (first character of name)
- ✅ Navigation avatar updates to letter avatar **without page refresh**
- ✅ Success toast message appears

---

## Test 4: Live Update After Upload (No Refresh)

**Steps:**
1. Open profile page in browser
2. Open Navigation in another tab
3. Upload new picture on profile page
4. Check Navigation avatar in the other tab

**Expected Results:**
- ✅ Profile picture updates immediately on profile page
- ✅ Profile picture updates immediately in Navigation avatar
- ✅ No page refresh required
- ✅ Navigation automatically syncs via AuthContext

---

## Test 5: Letter Avatar Fallback

**When it appears:**
- User has no profile picture (`profile_pic` is NULL)
- Image fails to load (404, network error, etc.)

**Expected:**
- ✅ Shows first character of user name in gray circle
- ✅ Example: "John Smith" shows "J"
- ✅ Works on both profile page and navigation
- ✅ Consistent styling with image avatar

---

## Test 6: Multiple Users

**Steps:**
1. Login as User A (user_id=1)
2. Upload picture → filename should be `1_HHMMDDMMYYYY.png`
3. Logout
4. Login as User B (user_id=2)
5. Upload picture → filename should be `2_HHMMDDMMYYYY.png`

**Expected:**
- ✅ Each user has unique picture filename based on ID
- ✅ No filename conflicts
- ✅ Both pictures display correctly
- ✅ Each user sees only their own picture

---

## Test 7: File Type Validation

**Try uploading these files:**
- ✅ JPG file → should work
- ✅ JPEG file → should work
- ✅ PNG file → should work
- ❌ GIF file → should reject with error message
- ❌ BMP file → should reject with error message
- ❌ WebP file → should reject with error message

**Expected:** Error toast: "Only JPG and PNG image files are allowed for profile photos."

---

## Test 8: Database Verification

**Using Laravel Tinker:**
```bash
cd backend/laravel-5scent
php artisan tinker
```

```php
// Check profile picture storage format
$user = User::find(1);
$user->profile_pic; // Should output: "1_213526112025.png"

// NOT: "profile_pics/1_213526112025.png"
// NOT: "/profile_pics/1_213526112025.png"
// NOT: "http://localhost:8000/..."
```

---

## API Endpoints (for testing)

### Upload Profile Picture
- **Route:** `POST /api/upload-profile`
- **Body:** FormData with `file`, `userId`, `oldFilename` (optional)
- **Response:** `{ path: "profile_pics/1_213526112025.png", filename: "1_213526112025.png" }`

### Update Profile with Picture
- **Route:** `PUT /profile`
- **Body:** FormData with `profile_pic_filename`, name, email, phone, address, etc.
- **Required:** Bearer token in Authorization header

### Remove Profile Picture
- **Route:** `DELETE /profile/picture`
- **Response:** `{ message: "Profile picture removed successfully" }`
- **Required:** Bearer token in Authorization header

---

## Common Issues & Solutions

### Issue: Picture shows on profile but not in navigation
**Solution:** AuthContext not updated. Check if `updateUser()` is called after upload.

### Issue: Picture doesn't load on different machine
**Solution:** Check if database stores full URL instead of just filename. Should be `"17_213526112025.png"` NOT `"http://localhost:8000/..."`

### Issue: Remove Photo button doesn't appear
**Solution:** Check if `preview` state is set. Button only shows when `preview` has a value.

### Issue: Letter avatar not showing on image load error
**Solution:** Ensure `onError` handler in image tag sets the hidden div to display. Check browser console for errors.

### Issue: Multiple images with same filename
**Solution:** Timestamp format includes hour, minute, second, day, month, year. Only collision if two users upload within same second. Very rare but can add milliseconds if needed.

---

## File Locations

### Frontend Files Modified
- `frontend/web-5scent/app/api/upload-profile/route.ts` - Upload handler
- `frontend/web-5scent/app/profile/page.tsx` - Profile page + remove handler
- `frontend/web-5scent/components/Navigation.tsx` - Navigation avatar

### Backend Files Modified
- `backend/laravel-5scent/app/Http/Controllers/ProfileController.php` - Delete endpoint + validation
- `backend/laravel-5scent/routes/api.php` - Route definition

### File Storage
- `frontend/web-5scent/public/profile_pics/` - Where pictures are stored

---

## Filename Format Reference

**Pattern:** `{user_id}_{HHMMDDMMYYYY}.{ext}`

**Breakdown:**
- `user_id` = Database user ID (e.g., 1, 17, 42)
- `_` = Separator
- `HH` = Hours (00-23)
- `MM` = Minutes (00-59)
- `DD` = Day (01-31)
- `MM` = Month (01-12)
- `YYYY` = Year (2025)
- `.` = Dot separator
- `ext` = Extension (jpg, jpeg, png)

**Examples:**
- `1_000000010122025.png` - User 1, 00:00:00 on Jan 1, 2025
- `17_213526112025.png` - User 17, 21:35:26 on Nov 26, 2025
- `42_120530050320245.jpg` - User 42, 12:05:30 on May 3, 2024

---

## Success Checklist

- [ ] File naming uses user_id instead of username
- [ ] Database stores only filename (not full path)
- [ ] Profile picture works on different machines
- [ ] Upload updates UI immediately (no refresh)
- [ ] Remove Photo button works (red button visible)
- [ ] Letter avatar fallback displays correctly
- [ ] Navigation avatar updates without refresh
- [ ] All validation errors show helpful messages
- [ ] PHP syntax validation passes
- [ ] No console errors in browser DevTools

---

## Notes for Future Maintenance

1. **Timestamp Format:** Currently HHMMDDMMYYYY (12 digits). Collision only if same user uploads twice in same second.
2. **File Storage:** Next.js public folder means files are served statically at `/profile_pics/{filename}`
3. **Cleanup:** Old files are automatically deleted when new ones are uploaded (handles by upload route)
4. **Database:** Only stores filename for portability. Frontend always constructs `/profile_pics/` prefix.
5. **AuthContext:** All UI updates use context propagation. No manual DOM manipulation needed.

---

✨ Implementation is complete and ready for testing!
