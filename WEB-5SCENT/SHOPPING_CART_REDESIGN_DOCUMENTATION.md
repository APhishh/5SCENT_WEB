# 🛒 Shopping Cart Page - Complete Redesign Documentation

**Date:** November 24, 2025  
**Status:** ✅ IMPLEMENTED & READY  
**Version:** 2.0 - Merged Products Layout

---

## 📋 Overview

The Shopping Cart page has been completely redesigned to provide a modern, user-friendly experience with merged product containers. When users add multiple sizes of the same perfume, they appear in ONE unified container instead of multiple separate cards.

**File Updated:** `app/cart/page.tsx`

---

## 🎯 Core Features Implemented

### 1. ✅ Merged Product Containers

**Behavior:**
- Each perfume brand gets ONE unified container
- Multiple sizes (30ml, 50ml) within the same container
- Product images displayed side-by-side with size badges
- Clean product header section

**Visual Structure:**
```
┌─────────────────────────────────────────┐
│ [Product Images] │ Product Info         │
│  [30ml] [50ml]   │ Name: Elegance Noir  │
│  Badge overlay   │ Category: Floral     │
│                  │ 2 sizes in cart      │
├─────────────────────────────────────────┤
│ Size Rows (Individual Controls)         │
│ ☑ Size 30ml  Rp89.000  [- 1 +] [delete]│
│ ☑ Size 50ml  Rp139.000 [- 1 +] [delete]│
└─────────────────────────────────────────┘
```

### 2. ✅ Product Images Section

**Features:**
- Up to 2 images displayed side-by-side
- Left image = first size added (30ml typically)
- Right image = second size added (50ml typically)
- Rounded corners on images
- Size badge overlay at bottom of each image
- Hover effect: image scales slightly (105%)
- Smooth transitions

**Size Badge:**
```
┌──────────────────┐
│                  │
│  Product Image   │
│                  │
│  [30ml]          │  ← Badge overlay
└──────────────────┘
```

**Badge Styling:**
- Background: Black (bg-black)
- Text: White, bold
- Opacity: 70% (bg-opacity-70)
- Padding: Small (py-1 px-2)
- Position: Bottom, full width
- Font size: xs (text-xs)

### 3. ✅ Individual Size Rows

**Layout per Size:**
```
[Checkbox] Size Label | Price | Stock Info | Qty Controls | Delete
    ☑        30ml   | Rp89K |  45 avail   | [- 1 +]    | [🗑]
```

**Components:**
| Element | Purpose | Style |
|---------|---------|-------|
| Checkbox | Select for checkout | w-5 h-5, cursor-pointer |
| Size Label | 30ml or 50ml | min-w-[70px], font-medium |
| Price | Product price | min-w-[100px], font-semibold |
| Stock Info | Available units | min-w-[140px], text-xs gray |
| Qty Controls | Adjust quantity | Border rounded, hover effect |
| Delete Button | Remove size | w-5 h-5, hover color change |

**Row Styling:**
- Background: Gray (bg-gray-50)
- Border: Gray 200 (border-gray-200)
- Padding: p-4
- Rounded: lg (rounded-lg)
- Hover: bg-gray-100 with smooth transition
- Gap between elements: gap-4

### 4. ✅ Checkbox Behavior

**Individual Checkboxes:**
- Each size has its own checkbox
- Users can select 30ml without 50ml
- Users can select 50ml without 30ml
- Unchecked items excluded from Order Summary

**Select All:**
- Top checkbox selects all items in cart
- Checking one item doesn't auto-check "Select All"
- "Select All" requires explicit click

**Order Summary Logic:**
```javascript
// Only selected items are included
const selectedTotal = items
  .filter(item => selectedItems.includes(item.cart_id))
  .reduce((sum, item) => sum + item.total, 0);
```

### 5. ✅ Quantity Controls

**Features:**
- Minus button: [-] reduce by 1
- Plus button: [+] increase by 1
- Display: Current quantity in center
- Quantity becomes 0 → Item auto-removed
- Border: Gray 300, rounded
- Hover effect: bg-gray-200

**Behavior:**
- Clicking [-] at quantity 1 → Removes item from cart
- Clicking [+] checks stock → Updates if available
- Real-time quantity updates

### 6. ✅ Delete Functionality

**Per-Size Delete:**
- Each row has a delete button (trash icon)
- Delete removes ONLY that size
- If 30ml deleted, 50ml stays in container
- If both deleted, container disappears

**Delete All:**
- Top "Delete All" button removes selected items
- Requires confirmation dialog
- Updates immediately after confirmation

**Confirmation Modal:**
```
┌────────────────────────────┐
│ Remove Item                │
│                            │
│ Are you sure you want to   │
│ remove Elegance Noir (30ml)│
│ from your cart?            │
│                            │
│  [Cancel]  [Delete]        │
└────────────────────────────┘
```

---

## 📊 Order Summary Section

### Layout

```
┌──────────────────────────┐
│  Order Summary           │
├──────────────────────────┤
│ Total Items        2     │
│ Subtotal      Rp228.000  │
│ Shipping      Free       │
│ Tax (5%)       Rp11.400  │
├──────────────────────────┤
│ Total         Rp239.400  │
├──────────────────────────┤
│ [Proceed to Checkout]    │
│ [Continue Shopping]      │
└──────────────────────────┘
```

### Components

**Static Position:**
- `sticky top-20` - Follows scroll, stays visible
- Right sidebar on desktop (md:col-span-2 grid layout)
- Full width on mobile

**Styling:**
- Background: Gray 50 (bg-gray-50)
- Rounded: lg
- Padding: p-6
- Border: Gray 200 (border-gray-200)
- Shadow: Subtle (shadow-sm hover:shadow-md)

**Summary Lines:**
```
Total Items     [Number of selected items]
Subtotal        [Sum of selected item totals]
Shipping        "Free" (green text)
Tax (5%)        [Subtotal × 0.05]
────────────────────────────
Total           [Subtotal + Tax]
```

### Buttons

**Primary Button:**
```
[Proceed to Checkout]
- Full width
- Black background (bg-black)
- White text
- Rounded lg
- Hover: bg-gray-800
- Disabled: opacity-50 if no items selected
```

**Secondary Button:**
```
[Continue Shopping]
- Full width
- Border 2px black (border-2 border-black)
- Black text
- Rounded lg
- Hover: bg-black, text-white
```

---

## 🎨 Visual Design Details

### Color Palette

| Element | Color | Usage |
|---------|-------|-------|
| Borders | Gray-200 | Dividers, container edges |
| Backgrounds | Gray-50 | Summary section, row hover |
| Text Primary | Gray-900 | Headings, prices |
| Text Secondary | Gray-600 | Labels, descriptions |
| Accent | Black | Buttons, badges |
| Success | Green-600 | "Free" shipping |
| Hover | Gray-100/200 | Row interactions |

### Spacing & Sizing

| Element | Spacing | Size |
|---------|---------|------|
| Container Gap | gap-8 | Main grid |
| Row Gap | gap-4 | Size row elements |
| Padding | p-6 | Container edges |
| Row Padding | p-4 | Size rows |
| Image Size | w-28 h-32 | Each image |
| Rounded | lg | Most containers |
| Border | 1px/2px | Varies |

### Typography

| Element | Font | Weight | Size |
|---------|------|--------|------|
| Page Title | Header font | bold | text-4xl |
| Section Title | Semibold | Medium | text-xl |
| Product Name | Semibold | Medium | text-lg |
| Size Label | Medium | Medium | text-sm |
| Price | Semibold | Medium | text-sm |
| Stock Info | Regular | Normal | text-xs |

### Interactive Elements

**Hover States:**
- Product containers: shadow-md
- Size rows: bg-gray-100
- Images: scale-105 (transform)
- Links: text color change
- Buttons: background/text color swap

**Transitions:**
- All hover effects: duration-300
- Smooth color transitions: transition-colors
- Scale transforms: transition-transform

---

## 🔄 Data Flow

### Product Grouping Logic

```typescript
const groupedProducts = useMemo(() => {
  const groups: { [key: number]: GroupedProduct } = {};

  // Group items by product_id
  items.forEach((item) => {
    const productId = item.product.product_id;

    if (!groups[productId]) {
      groups[productId] = {
        productId,
        productName: item.product.name,
        category: item.product.category,
        images: [],
        sizes: [],
        cartItems: [],
      };
    }

    // Add this item's data to the group
    groups[productId].cartItems.push(item);
    groups[productId].sizes.push(item.size);
    
    // Get size-specific image
    const sizeImage = item.product.images.find(
      (img: any) => 
        (item.size === '30ml' && img.is_50ml === 0) ||
        (item.size === '50ml' && img.is_50ml === 1)
    );
    
    if (sizeImage) {
      groups[productId].images.push(sizeImage.image_url);
    }
  });

  return Object.values(groups);
}, [items]);
```

### Image Ordering

**Logic:**
- Images added in order of size addition
- If 30ml added first → left image
- If 50ml added first → left image
- Maximum 2 images per product

**Stock Display:**
```typescript
Stock: {
  item.size === '30ml' 
    ? item.product.stock_30ml 
    : item.product.stock_50ml
} available
```

---

## 🧪 User Scenarios

### Scenario 1: User adds 30ml then 50ml of same perfume

**Step 1:** Add 30ml
```
Product Container appears with:
- 1 image (30ml) on left
- 1 size row: 30ml
```

**Step 2:** Add 50ml
```
Product Container updates to:
- 2 images (30ml left, 50ml right)
- 2 size rows: 30ml and 50ml
```

### Scenario 2: User selects only 50ml, unselects 30ml

**Before:**
```
Order Summary shows:
- Total Items: 2
- Subtotal: Rp228.000 (both sizes)
```

**After Unchecking 30ml:**
```
Order Summary updates to:
- Total Items: 1
- Subtotal: Rp139.000 (50ml only)
```

### Scenario 3: User deletes 30ml

**Before:**
```
Container shows:
- 2 images (30ml, 50ml)
- 2 rows
```

**After Delete:**
```
Container shows:
- 1 image (50ml only)
- 1 row (50ml)
```

### Scenario 4: User reduces 50ml quantity to 0

**Behavior:**
```
- Quantity at 0 automatically triggers delete
- No confirmation needed (delete happens directly)
- 30ml row remains if it exists
- Container still visible with remaining sizes
```

---

## 📱 Responsive Design

### Desktop (md and above)
```
┌─────────────────────────────────────────────┐
│ Products List (2/3)   │ Order Summary (1/3) │
├─────────────────────────────────────────────┤
│ 2 columns layout    │ Sticky sidebar       │
│ Full product cards  │ top-20 position      │
└─────────────────────────────────────────────┘
```

### Mobile (below md)
```
┌──────────────────────────┐
│ Products List (full)     │
├──────────────────────────┤
│ Order Summary (full)     │
└──────────────────────────┘
```

**Breakpoints:**
- md:col-span-2 = 2 columns on desktop
- Full width on smaller screens
- Stack vertically on mobile

---

## 🎯 Key Implementation Details

### File: `app/cart/page.tsx`

**Lines Modified:**
- State management: Added GroupedProduct interface
- useMemo hook: Groups products by ID
- JSX structure: Replaced individual items with grouped layout
- Image rendering: Multiple images per container
- Size rows: Individual controls for each variant

**Components Used:**
- Navigation, Footer (existing)
- useCart, useAuth, useToast (context)
- Image (Next.js optimized)
- Heroicons (trash, shopping bag)

**Key Functions:**
```typescript
// Group items by product
const groupedProducts = useMemo(() => { ... }, [items])

// Handle quantity changes
const handleQuantityChange = (itemId, newQuantity) => { ... }

// Handle item deletion
const handleRemove = (itemId, itemName, size) => { ... }

// Handle checkout
const handleCheckout = () => { ... }
```

---

## ✅ Feature Checklist

- [x] Merged product containers
- [x] Multiple images per product
- [x] Size badges on images
- [x] Individual checkboxes per size
- [x] Individual quantity controls
- [x] Individual delete buttons
- [x] Correct image ordering
- [x] Stock information display
- [x] Order Summary updates based on selection
- [x] Delete All functionality
- [x] Delete confirmation modal
- [x] Smooth transitions and hover effects
- [x] Sticky order summary sidebar
- [x] Mobile responsive layout
- [x] Modern minimalist design
- [x] Proper spacing and alignment

---

## 🚀 Testing Scenarios

### ✅ Test 1: Add Multiple Sizes
1. Add 30ml of Elegance Noir
2. Add 50ml of Elegance Noir
3. **Expected:** Single container with 2 images and 2 rows

### ✅ Test 2: Individual Selection
1. Have 30ml and 50ml in cart
2. Uncheck 30ml, keep 50ml checked
3. **Expected:** Order Summary shows only 50ml price

### ✅ Test 3: Delete One Size
1. Have 30ml and 50ml in container
2. Click delete on 30ml row
3. **Expected:** 30ml row disappears, 30ml image removed, 50ml remains

### ✅ Test 4: Quantity to Zero
1. Have item with quantity 1
2. Click minus button
3. **Expected:** Item automatically deleted from cart

### ✅ Test 5: Delete All
1. Have multiple items selected
2. Click "Delete All" button
3. Click confirm
4. **Expected:** All selected items removed instantly

### ✅ Test 6: Responsive
1. View on desktop → 2 column layout
2. View on mobile → 1 column layout
3. **Expected:** Layout adapts correctly

---

## 📚 Component Integration

**Existing Components Used:**
- `Navigation` - Header component
- `Footer` - Footer component
- `useAuth` - User authentication
- `useCart` - Cart state management
- `useToast` - Toast notifications
- `formatCurrency` - Price formatting utility
- Heroicons - SVG icons

**No New Dependencies Added:**
- Uses existing React hooks (useState, useEffect, useMemo)
- Uses existing Tailwind classes
- Uses existing Image component from Next.js

---

## 🎉 Design Highlights

1. **Modern Minimalist Design**
   - Clean lines, spacious layout
   - Subtle shadows instead of bold borders
   - Professional color palette

2. **User-Friendly**
   - Intuitive controls per size
   - Clear visual hierarchy
   - Obvious call-to-action buttons

3. **Responsive**
   - Adapts to all screen sizes
   - Sticky order summary on desktop
   - Mobile-optimized layout

4. **Smooth Interactions**
   - Hover effects on all interactive elements
   - Smooth transitions and animations
   - Instant visual feedback

5. **Accessible**
   - Clear labels and descriptions
   - Proper form controls (checkboxes)
   - Readable typography and contrast

---

## 📝 Notes

- **Image Optimization:** Uses Next.js Image component with unoptimized flag for development
- **Performance:** useMemo prevents unnecessary grouping recalculations
- **Accessibility:** All inputs have proper labels and semantic HTML
- **Mobile:** Full-width buttons and adjusted spacing for touch targets

---

**Status: ✅ COMPLETE & PRODUCTION READY**

The Shopping Cart page now features a modern, merged-product layout with individual size controls, providing an excellent user experience for managing fragrance purchases with multiple size variants.

