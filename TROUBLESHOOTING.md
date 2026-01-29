# Hướng dẫn xử lý lỗi Tailwind CSS không hoạt động

## Các bước cần làm khi CSS không hoạt động sau khi build:

### 1. Clear Cache và Rebuild
```bash
# Xóa cache của Vite
rm -rf node_modules/.vite
rm -rf public/build
rm -rf public/hot

# Clear Laravel cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Rebuild CSS
npm run build
# hoặc nếu đang dev
npm run dev
```

### 2. Kiểm tra Browser Cache
- Hard refresh: `Ctrl + Shift + R` (Windows) hoặc `Cmd + Shift + R` (Mac)
- Hoặc mở DevTools > Network > Disable cache

### 3. Kiểm tra file build
- Đảm bảo file `public/build/assets/app-*.css` được tạo mới
- Kiểm tra trong file CSS đã build xem có class `.border-primary` và `.text-primary` không

### 4. Nếu vẫn không hoạt động, thử:

#### Option A: Sử dụng inline styles (đã implement)
Code đã được sửa để dùng inline styles với `!important` để đảm bảo hoạt động.

#### Option B: Thêm CSS trực tiếp vào @push('styles')
Nếu Tailwind utilities không hoạt động, có thể thêm CSS trực tiếp vào section styles trong blade file.

### 5. Kiểm tra Vite config
Đảm bảo `vite.config.js` có:
```js
import tailwindcss from '@tailwindcss/vite';
// ...
plugins: [
    tailwindcss(),
]
```

### 6. Kiểm tra package.json
Đảm bảo có:
- `"tailwindcss": "^4.1.18"`
- `"@tailwindcss/vite": "^4.0.0"`

### 7. Nếu dùng Tailwind v4, cần lưu ý:
- Tailwind v4 sử dụng `@theme` thay vì `tailwind.config.js`
- Các utility classes cần được định nghĩa trong `@layer utilities`
- Có thể cần dùng CSS variables với format `rgb()` thay vì hex

## Các class đã được định nghĩa trong app.css:
- `.border-primary` - Border màu cam
- `.text-primary` - Text màu cam  
- `.bg-primary` - Background màu cam
- `.bg-primary/5`, `.bg-primary/10`, `.bg-primary/20` - Background với opacity
- `.hover:border-primary/50` - Hover border với opacity

## Fallback đã được implement:
Nếu Tailwind utilities không hoạt động, code đã có fallback với:
- Inline styles với `!important`
- Hardcoded colors trong CSS
- JavaScript để set style trực tiếp












