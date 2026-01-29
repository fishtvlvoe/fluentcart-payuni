# Frontend Development Guide | FluentCart Developer Docs

URL: https://dev.fluentcart.com/guides/frontend.html

---


# Frontend Development Guide ​

This guide covers frontend development for FluentCart, including Vue.js integration, template customization, and asset management.


## Template System ​


### Template Hierarchy ​

FluentCart uses a comprehensive template system with PHP templates for frontend display and Vue.js components for admin interface:


```
app/FC/Template/DefaultTemplate/Views/
├── checkout/
│   ├── checkout.php
│   ├── payment-methods/
│   ├── forms/
│   ├── inputs/
│   └── order/
├── cart/
│   ├── cart-drawer.php
│   ├── cart-content-wrapper.php
│   └── list/
├── product-card/
│   ├── single-product.php
│   └── components/
├── single-product-page/
│   ├── single-product.php
│   └── components/
├── shop/
│   ├── shop.php
│   ├── filters/
│   └── list/
└── pricing-table/
    ├── wrapper.php
    └── components/
```


### Custom Templates ​

FluentCart supports template overrides through WordPress's locate_template function:

php 
```
// Templates are automatically located in this order:
// 1. yourtheme/fluent-cart/{template-name}.php
// 2. plugin/templates/{template-name}.php
// 3. plugin fallback template

// Example: Override checkout template
// Create: yourtheme/fluent-cart/checkout/checkout.php
```


### Template Loading ​

Templates are loaded through the TemplateLoader class:

php 
```
use FluentCart\App\FC\Template\DefaultTemplate\TemplateLoader;

// Load a template part
$templateLoader = new TemplateLoader($app);
$templateLoader->loadTemplate('checkout', 'payment-methods');
```


### Template Hooks ​

FluentCart provides extensive template hooks for customization:

php 
```
// Product page hooks
add_action('fluent_cart_single_product_summary', function() {
    echo '<div class="custom-product-info">Custom content here</div>';
}, 20);

// Checkout page hooks
add_action('fluent_cart_checkout_before_order_review', function() {
    echo '<div class="checkout-notice">Important notice</div>';
});

// Cart page hooks
add_action('fluent_cart_cart_table_after', function() {
    echo '<div class="cart-recommendations">Recommended products</div>';
});
```


## Vue.js Integration ​


### Admin Interface ​

FluentCart uses Vue.js 3 with Element Plus for the admin interface. The admin components are located in resources/admin/:


```
resources/admin/
├── Application.vue
├── Bits/Components/     # 337 Vue components
├── Modules/            # Feature-specific components
│   ├── Products/       # 43 components
│   ├── Orders/         # 34 components
│   ├── Customers/      # 19 components
│   └── Reports/        # 73 components
├── Pages/              # Page components
└── utils/              # Utilities and mixins
```


### Frontend Vue Components ​

Frontend Vue components are located in resources/public/:


```
resources/public/
├── cart/Cart.js
├── checkout/           # 19 components
├── customer-profile/   # 74 components
├── product-card/       # 3 components
├── pricing-table/      # 4 components
└── search-bar-app/     # 3 components
```


### Component Example ​

vue 
```
<template>
  <div class="fluent-cart-component">
    <el-button @click="handleClick" type="primary">
      {{ buttonText }}
    </el-button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ElButton } from 'element-plus'

const props = defineProps({
  buttonText: {
    type: String,
    default: 'Click Me'
  }
})

const emit = defineEmits(['click'])

const handleClick = () => {
  emit('click')
}
</script>
```


### React Integration ​

FluentCart also includes React components for Gutenberg blocks:


```
resources/admin/BlockEditor/
├── Buttons/            # Add to Cart, Direct Checkout
├── Checkout/           # Checkout block
├── CustomerProfile/    # Customer profile block
├── PricingTable/       # Pricing table block
├── ProductCard/        # Product card block
└── SearchBar/          # Search bar block
```


### State Management ​

For complex applications, consider using Pinia for state management:

javascript 
```
import { createPinia } from 'pinia'
import { defineStore } from 'pinia'

// Cart store
export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        total: 0,
        loading: false
    }),
    
    actions: {
        async addToCart(productId, quantity = 1) {
            this.loading = true
            try {
                const response = await fetch('/wp-json/fluent-cart/v1/cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': window.fluentCart.nonce
                    },
                    body: JSON.stringify({
                        action: 'add_to_cart',
                        product_id: productId,
                        quantity: quantity
                    })
                })
                
                const data = await response.json()
                this.items = data.items
                this.total = data.total
            } catch (error) {
                console.error('Error adding to cart:', error)
            } finally {
                this.loading = false
            }
        }
    }
})

// Initialize Pinia
const pinia = createPinia()
app.use(pinia)
```


### Event Handling ​

Handle FluentCart events in Vue components:

javascript 
```
// Listen for FluentCart events
window.addEventListener('fluent-cart:cart-updated', (event) => {
    // Update Vue component state
    this.items = event.detail.items
    this.total = event.detail.total
})

// Dispatch custom events
window.dispatchEvent(new CustomEvent('fluent-cart:custom-action', {
    detail: { productId: 123, action: 'view' }
}))
```


## Asset Management ​


### Vite Configuration ​

FluentCart uses Vite for asset bundling and development:

javascript 
```
// vite.config.mjs
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    build: {
        rollupOptions: {
            input: {
                'checkout': 'resources/js/checkout.js',
                'cart': 'resources/js/cart.js',
                'product': 'resources/js/product.js'
            }
        }
    }
})
```


### SCSS Compilation ​

Organize styles with SCSS:

scss 
```
// resources/scss/checkout.scss
@import 'variables';
@import 'mixins';

.fluent-cart-checkout {
    &__form {
        @include form-styles;
        
        &-group {
            margin-bottom: 1rem;
        }
    }
    
    &__payment-methods {
        @include payment-methods-styles;
    }
}

// Variables
$primary-color: #007cba;
$border-radius: 4px;
$spacing: 1rem;

// Mixins
@mixin form-styles {
    .form-control {
        border: 1px solid #ddd;
        border-radius: $border-radius;
        padding: 0.5rem;
        
        &:focus {
            border-color: $primary-color;
            outline: none;
        }
    }
}
```


### JavaScript Bundling ​

Structure JavaScript files for optimal loading:

javascript 
```
// resources/js/checkout.js
import { createApp } from 'vue'
import CheckoutComponent from './components/Checkout.vue'
import '../scss/checkout.scss'

// Initialize checkout app
const checkoutApp = createApp(CheckoutComponent)
checkoutApp.mount('#checkout-app')

// Lazy load additional components
const loadPaymentMethods = () => {
    return import('./components/PaymentMethods.vue')
}

// Load payment methods when needed
document.addEventListener('DOMContentLoaded', () => {
    const paymentSection = document.querySelector('#payment-methods')
    if (paymentSection) {
        loadPaymentMethods().then(module => {
            checkoutApp.component('PaymentMethods', module.default)
        })
    }
})
```


## Responsive Design ​


### Mobile-First Approach ​

Design for mobile devices first:

scss 
```
// Mobile-first responsive design
.fluent-cart-product {
    display: block;
    margin-bottom: 1rem;
    
    // Tablet and up
    @media (min-width: 768px) {
        display: flex;
        gap: 1rem;
    }
    
    // Desktop and up
    @media (min-width: 1024px) {
        gap: 2rem;
    }
}

.fluent-cart-checkout {
    &__form {
        // Mobile: single column
        display: block;
        
        // Tablet: two columns
        @media (min-width: 768px) {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    }
}
```


### Touch-Friendly Interface ​

Ensure touch-friendly interactions:

scss 
```
.fluent-cart-button {
    min-height: 44px; // Minimum touch target
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    
    &:active {
        transform: scale(0.98); // Touch feedback
    }
}

.fluent-cart-input {
    min-height: 44px;
    padding: 0.75rem;
    font-size: 16px; // Prevent zoom on iOS
    
    @media (max-width: 767px) {
        font-size: 16px; // Ensure no zoom
    }
}
```


## Performance Optimization ​


### Lazy Loading ​

Implement lazy loading for images and components:

javascript 
```
// Lazy load product images
const lazyImages = document.querySelectorAll('img[data-src]')

const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target
            img.src = img.dataset.src
            img.classList.remove('lazy')
            imageObserver.unobserve(img)
        }
    })
})

lazyImages.forEach(img => imageObserver.observe(img))
```


### Code Splitting ​

Split code for better performance:

javascript 
```
// Dynamic imports for route-based code splitting
const routes = [
    {
        path: '/checkout',
        component: () => import('./views/Checkout.vue')
    },
    {
        path: '/cart',
        component: () => import('./views/Cart.vue')
    }
]
```


### Caching Strategies ​

Implement proper caching:

javascript 
```
// Service worker for caching
self.addEventListener('fetch', (event) => {
    if (event.request.url.includes('/wp-json/fluent-cart/')) {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request).then(fetchResponse => {
                    return caches.open('fluent-cart-v1').then(cache => {
                        cache.put(event.request, fetchResponse.clone())
                        return fetchResponse
                    })
                })
            })
        )
    }
})
```

Related Documentation:

- Template System - Module development
- REST API - API integration
- Developer Hooks - Customization hooks

