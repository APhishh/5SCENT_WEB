# 🎨 Shopping Cart UI - Visual Reference Guide

## 📐 Layout Structure

### Full Page Layout (Desktop)
```
┌─────────────────────────────────────────────────────────────────┐
│                          NAVIGATION BAR                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Shopping Cart                                                  │
│  ━━━━━━━━━━━━                                                   │
│                                                                 │
├────────────────────────────────────┬──────────────────────────┤
│                                    │                          │
│  CART ITEMS                        │   ORDER SUMMARY          │
│  (Left 2/3)                        │   (Right 1/3)            │
│                                    │                          │
│  ┌────────────────────────────┐   │  ┌────────────────────┐  │
│  │ Select All | Delete All    │   │  │ Order Summary      │  │
│  ├────────────────────────────┤   │  ├────────────────────┤  │
│  │                            │   │  │                    │  │
│  │ [PRODUCT CONTAINER]        │   │  │ Total Items    2   │  │
│  │                            │   │  │ Subtotal       xxx │  │
│  │ Size Rows (stacked)        │   │  │ Shipping      Free│  │
│  │                            │   │  │ Tax (5%)       xxx │  │
│  │ [PRODUCT CONTAINER]        │   │  ├────────────────────┤  │
│  │                            │   │  │ Total          xxx │  │
│  │ Size Rows (stacked)        │   │  ├────────────────────┤  │
│  │                            │   │  │ [Proceed...]       │  │
│  └────────────────────────────┘   │  │ [Continue Shop]    │  │
│                                    │  └────────────────────┘  │
│                                    │                          │
└────────────────────────────────────┴──────────────────────────┘
│                            FOOTER                              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎁 Product Container (Merged Layout)

### Container Structure
```
┌─────────────────────────────────────────────────────────────┐
│                   PRODUCT HEADER                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [Image 1]    [Image 2]  │  Product Name                   │
│  [30ml]       [50ml]     │  Category                       │
│  (w-28 h-32)  (w-28)     │  2 sizes in cart                │
│               (h-32)     │                                 │
│                          │                                 │
├─────────────────────────────────────────────────────────────┤
│                   SIZE ROWS                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ☑  30ml  Rp89.000  Stock: 45  [−] 1 [+]  [🗑]            │
│                                                             │
│  ☑  50ml  Rp139.000 Stock: 45  [−] 1 [+]  [🗑]            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Styling Details
- **Container:** `bg-white border border-gray-200 rounded-lg p-6`
- **Hover:** `shadow-md` transition
- **Images Gap:** `gap-3 flex-shrink-0`
- **Image Size:** `w-28 h-32`
- **Image Border:** `rounded-lg`
- **Badge:** Positioned `bottom-0 left-0 right-0`

---

## 📸 Product Images Section

### Image Component
```
┌──────────────────┐
│                  │
│   [30ml Image]   │  ← Image fill container
│                  │
│  ┗━━━[30ml]━━━┛  ← Size Badge (black overlay)
└──────────────────┘

Dimensions: w-28 h-32 (7rem × 8rem)
Corners: rounded-lg
Hover: scale-105 on transform
Background: bg-gray-100 (loading state)
```

### Size Badge Overlay
```
Position: absolute bottom-0 left-0 right-0
Background: bg-black bg-opacity-70
Text: text-white text-xs font-semibold
Padding: py-1 px-2
Content: "30ml" or "50ml"
```

---

## 🔢 Size Row Layout

### Full Row Structure
```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  [☑] 30ml │ Rp89.000 │ Stock: 45 available │ [−]1[+]│[🗑]  │
│           │          │                     │        │      │
│  w-5 h-5  │ min-w-   │  min-w-[140px]      │  qty   │ w-5  │
│  flex-    │ [100px]  │  text-xs gray-600   │ ctrl   │ h-5  │
│  shrink-0 │ font-    │                     │        │      │
│           │ semibold │                     │        │      │
│           │          │                     │        │      │
└─────────────────────────────────────────────────────────────┘

Background: bg-gray-50
Border: border border-gray-200
Rounded: rounded-lg
Padding: p-4
Hover: bg-gray-100 transition
Gap: gap-4
```

### Size Row Spacing
```
[Checkbox]  [Size]  [Price]  [Stock]  [Qty]  [Delete]
   ☑          30ml   Rp89K   45 avail [ctrl]  [🗑]
             min-w   min-w   min-w
             [70px]  [100px] [140px]
```

---

## 🔢 Quantity Controls

### Control Component
```
┌─────────────────┐
│  [−]  1  [+]    │  ← Current quantity
└─────────────────┘

Button Width: w-8
Button Height: h-8
Center Display: w-8 text-center
Border: border border-gray-300
Rounded: rounded-lg
Text Size: text-sm
Hover: hover:bg-gray-200

Logic:
- At 1, clicking [−] deletes item
- Each [+] increases by 1
- Check stock before updating
```

---

## ☑️ Checkbox Behavior

### Individual Checkbox
```
Size:       w-5 h-5
Cursor:     cursor-pointer
Margin:     flex-shrink-0

When checked: ☑
When unchecked: ☐

Behavior:
- Toggle independent of other checkboxes
- Affects Order Summary calculation
- Select All updates based on all items
```

### Select All Master Checkbox
```
Position: Top of cart list
Text:     "Select all (5 items)"

When all items checked:  ☑ Select all
When partial checked:    ☐ Select all
When none checked:       ☐ Select all

Clicking updates all items instantly
```

---

## 🗑️ Delete Button

### Button Styling
```
Icon:       TrashIcon from Heroicons
Size:       w-5 h-5
Color:      text-black
Hover:      text-gray-700 transition-colors
Position:   ml-auto (right aligned)
Cursor:     pointer

Behavior:
- Click opens confirmation modal
- Modal shows product name + size
- Can cancel or confirm delete
```

### Delete Confirmation Modal
```
┌──────────────────────────────────┐
│  Remove Item                     │
│                                  │
│  Are you sure you want to remove │
│  Elegance Noir (30ml)            │
│  from your cart?                 │
│                                  │
│          [Cancel]  [Delete]      │
└──────────────────────────────────┘

Fixed backdrop: bg-black bg-opacity-50
Card: bg-white rounded-lg p-8
Max width: max-w-sm
z-index: z-50 (above everything)

Cancel: border-2 border-gray-300 hover:bg-gray-50
Delete: bg-red-600 hover:bg-red-700
```

---

## 📊 Order Summary Sidebar

### Sidebar Container
```
┌──────────────────────────┐
│  Order Summary           │  ← Title
├──────────────────────────┤
│                          │
│  Total Items:       2    │
│  Subtotal:    Rp228.000  │
│  Shipping:         Free  │  ← Green text
│  Tax (5%):    Rp11.400   │
│                          │
├──────────────────────────┤  ← Divider
│  Total:       Rp239.400  │
│                          │
├──────────────────────────┤
│ [Proceed to Checkout]    │
│ [Continue Shopping]      │
└──────────────────────────┘

Background: bg-gray-50
Border: border border-gray-200
Rounded: rounded-lg
Padding: p-6
Position: sticky top-20
```

### Order Summary Lines
```
Line Format:
┌────────────────────────┐
│ Label          │ Value │
├────────────────────────┤
│ Total Items    │   2   │
│ Subtotal       │ Rp.xx │
│ Shipping       │ Free  │ (green)
│ Tax (5%)       │ Rp.xx │
├────────────────────────┤
│ Total          │ Rp.xx │
└────────────────────────┘

Label: text-sm text-gray-600 (left aligned)
Value: text-sm font-medium text-gray-900 (right aligned)
Spacing: space-y-3
```

### Summary Buttons
```
PRIMARY BUTTON:
┌────────────────────────────┐
│ Proceed to Checkout        │
└────────────────────────────┘
Background: bg-black
Text: text-white font-semibold
Width: w-full
Padding: px-6 py-3
Rounded: rounded-lg
Hover: bg-gray-800
Disabled: opacity-50 cursor-not-allowed
Margin bottom: mb-3

SECONDARY BUTTON:
┌────────────────────────────┐
│ Continue Shopping          │
└────────────────────────────┘
Background: transparent
Border: border-2 border-black
Text: text-black font-semibold
Width: w-full
Padding: px-6 py-3
Rounded: rounded-lg
Hover: bg-black text-white
Margin: (none)
```

---

## 📱 Mobile Layout

### Full Width Stack
```
┌──────────────────────────┐
│   NAVIGATION BAR         │
├──────────────────────────┤
│  Shopping Cart           │
│  ━━━━━━━━━━━━            │
├──────────────────────────┤
│                          │
│  Select All              │
│  Delete All              │
│                          │
│  ┌────────────────────┐  │
│  │ PRODUCT            │  │
│  │ ┌────────────────┐ │  │
│  │ │ Size Rows      │ │  │
│  │ │ stacked        │ │  │
│  │ └────────────────┘ │  │
│  └────────────────────┘  │
│                          │
│  ┌────────────────────┐  │
│  │ ORDER SUMMARY      │  │
│  │ (full width)       │  │
│  └────────────────────┘  │
│                          │
├──────────────────────────┤
│      FOOTER              │
└──────────────────────────┘
```

---

## 🎨 Color Reference

### Background Colors
- **White (containers):** `bg-white`
- **Gray 50 (summary, rows):** `bg-gray-50`
- **Gray 100 (hover):** `bg-gray-100`
- **Gray 200 (hover button):** `bg-hover-200`
- **Black (buttons, badges):** `bg-black`
- **Red 600 (delete):** `bg-red-600`
- **Red 700 (delete hover):** `bg-red-700`

### Text Colors
- **Primary (headings):** `text-gray-900`
- **Secondary (labels):** `text-gray-600`
- **Tertiary (hints):** `text-gray-500`
- **White (on dark):** `text-white`
- **Success (free):** `text-green-600`
- **Hover:** `text-gray-700`

### Border Colors
- **Borders:** `border-gray-200`
- **Dark borders:** `border-gray-300`

---

## 🎭 Interaction States

### Checkbox States
```
Unchecked: ☐
Checked:   ☑
Hover:     Cursor pointer, subtle highlight
Focus:     Blue outline (browser default)
```

### Button States
```
Default:    Full opacity, static
Hover:      Color change, cursor pointer
Active:     Press animation (browser default)
Disabled:   opacity-50, cursor-not-allowed
Loading:    (spinner if needed)
```

### Row States
```
Default:    bg-gray-50
Hover:      bg-gray-100, shadow-md on container
Focus:      Within checkbox/button
Selected:   (via checkbox, not visual)
```

---

## 🌈 Typography Hierarchy

### Headings
- **Page Title:** text-4xl font-bold font-header
- **Section Title:** text-xl font-semibold
- **Row Label:** text-sm font-medium

### Body Text
- **Labels:** text-sm font-regular
- **Values:** text-sm font-medium or font-semibold
- **Small Text:** text-xs font-regular

### Font Usage
- **Header Font:** Branding/titles
- **Body Font:** Regular content

---

## ✨ Hover Effects

### Container Hover
```
From: shadow-sm
To:   shadow-md
Duration: 300ms
Property: transition-shadow
```

### Image Hover
```
From: scale-100
To:   scale-105
Duration: 300ms
Property: transition-transform
```

### Row Hover
```
From: bg-gray-50
To:   bg-gray-100
Duration: Fast (default)
Property: transition-colors
```

### Button Hover
```
Color Change + Cursor Pointer
Text Color Swap (for secondary button)
Duration: Fast transitions
```

---

## 📏 Responsive Breakpoints

### Desktop (md and above)
- Grid: md:grid-cols-3
- Main: md:col-span-2 (2/3 width)
- Sidebar: 1/3 width
- Sidebar: sticky top-20
- Layout: Side-by-side

### Tablet & Mobile (below md)
- Grid: grid-cols-1 (full width)
- Main: Full width
- Sidebar: Full width
- Sidebar: Not sticky (below main)
- Layout: Stacked vertically
- Padding: Adjusted for touch targets

---

## 🔧 Implementation Checklist

### Structure
- [x] Product grouping logic
- [x] Merged containers
- [x] Size rows
- [x] Order summary

### Styling
- [x] Colors and backgrounds
- [x] Spacing and padding
- [x] Typography
- [x] Borders and shadows
- [x] Rounded corners
- [x] Hover effects

### Functionality
- [x] Checkbox selection
- [x] Quantity controls
- [x] Delete functionality
- [x] Delete all
- [x] Order summary calculation
- [x] Confirmation modal
- [x] Checkout routing

### Responsive
- [x] Desktop layout
- [x] Mobile layout
- [x] Sidebar sticky
- [x] Touch targets
- [x] Font scaling

---

**Visual Design Complete & Ready for Implementation!**

