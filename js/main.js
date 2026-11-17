/**
* Template Name: Kelly
* Template URL: https://bootstrapmade.com/kelly-free-bootstrap-cv-resume-html-template/
* Updated: Aug 07 2024 with Bootstrap v5.3.3
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  mobileNavToggleBtn.addEventListener('click', mobileNavToogle);

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Animate the skills items on reveal
   */
  let skillsAnimation = document.querySelectorAll('.skills-animation');
  skillsAnimation.forEach((item) => {
    new Waypoint({
      element: item,
      offset: '80%',
      handler: function(direction) {
        let progress = item.querySelectorAll('.progress .progress-bar');
        progress.forEach(el => {
          el.style.width = el.getAttribute('aria-valuenow') + '%';
        });
      }
    });
  });

  /**
   * Initiate Pure Counter
   */
  new PureCounter();

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Initiate glightbox
   */
  if (typeof GLightbox !== 'undefined') {
    const glightbox = GLightbox({
      selector: '.glightbox'
    });
  }

  /**
   * Init isotope layout and filters
   */
  document.querySelectorAll('.isotope-layout').forEach(function(isotopeItem) {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), function() {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort
      });
    });

    isotopeItem.querySelectorAll('.isotope-filters li').forEach(function(filters) {
      filters.addEventListener('click', function() {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        if (typeof aosInit === 'function') {
          aosInit();
        }
      }, false);
    });

  });

  /**
   * Simple cart (localStorage)
   */
  const CART_STORAGE_KEY = 'foa_cart_items';

  function readCart() {
    try {
      const raw = localStorage.getItem(CART_STORAGE_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (error) {
      return [];
    }
  }

  function writeCart(items) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
  }

  function parsePrice(priceText) {
    const numeric = (priceText || '').replace(/[^0-9.]/g, '');
    return Number.parseFloat(numeric) || 0;
  }

  function formatPrice(value) {
    return `Rs ${value.toLocaleString('en-LK', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })}`;
  }

  function activateInitialSelection(container) {
    const firstSize = container.querySelector('.size-options .size-btn');
    const firstSwatch = container.querySelector('.color-swatches .swatch, .color-options .color-swatch');

    if (firstSize && !container.querySelector('.size-options .size-btn.selected')) {
      firstSize.classList.add('selected');
    }

    if (firstSwatch && !container.querySelector('.color-swatches .swatch.selected, .color-options .color-swatch.selected')) {
      firstSwatch.classList.add('selected');
    }
  }

  function initCollectionProductCards() {
    // Handle both .collection-item and .product-item classes
    const cards = document.querySelectorAll('.collection-item, .product-item');
    if (!cards.length) return;

    cards.forEach((card) => {
      activateInitialSelection(card);

      // Handle size buttons
      card.querySelectorAll('.size-options .size-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          card.querySelectorAll('.size-options .size-btn').forEach((item) => item.classList.remove('selected'));
          btn.classList.add('selected');
        });
      });

      // Handle color swatches (both old .swatch and new .color-swatch)
      const swatches = card.querySelectorAll('.color-swatches .swatch, .color-options .color-swatch');
      swatches.forEach((swatch) => {
        swatch.addEventListener('click', () => {
          card.querySelectorAll('.color-swatches .swatch, .color-options .color-swatch').forEach((item) => item.classList.remove('selected'));
          swatch.classList.add('selected');
        });
      });

      // Handle add to cart button (both old .btn-add-to-cart and new .btn-add-cart)
      const addButton = card.querySelector('.btn-add-to-cart, .btn-add-cart');
      if (!addButton) return;

      addButton.addEventListener('click', () => {
        const title = (card.querySelector('h3')?.textContent || 'Product').trim();
        const priceText = (card.querySelector('.price')?.textContent || 'Rs 0').trim();
        const price = parsePrice(priceText);
        
        // Handle both old .collection-img and new .product-image
        const img = card.querySelector('.collection-img img, .product-image img');
        const image = img?.getAttribute('src') || '';

        const selectedSizeBtn = card.querySelector('.size-options .size-btn.selected') || card.querySelector('.size-options .size-btn');
        const selectedSwatch = card.querySelector('.color-swatches .swatch.selected, .color-options .color-swatch.selected') || 
                             card.querySelector('.color-swatches .swatch, .color-options .color-swatch');

        const size = (selectedSizeBtn?.textContent || 'S').trim();
        
        let color = 'Default';
        if (selectedSwatch) {
          const title = selectedSwatch.getAttribute('title');
          if (title) {
            color = title.trim();
          } else {
            const styleAttr = selectedSwatch.getAttribute('style') || '';
            const bgColorMatch = styleAttr.match(/background-color:\s*([^;]+)/);
            color = (bgColorMatch ? bgColorMatch[1].trim() : getComputedStyle(selectedSwatch).backgroundColor).trim();
          }
        }

        const cart = readCart();
        const existingIndex = cart.findIndex(
          (item) => item.name === title && item.size === size && item.color === color
        );

        if (existingIndex >= 0) {
          cart[existingIndex].quantity += 1;
        } else {
          cart.push({
            id: `${title}-${color}-${size}`,
            name: title,
            color: color,
            size: size,
            unitPrice: price,
            image: image,
            quantity: 1
          });
        }

        writeCart(cart);
        updateCartBadge();
        window.location.href = 'cart.html';
      });
    });
  }

  function getCartTotal(cart) {
    return cart.reduce((total, item) => total + (item.unitPrice * item.quantity), 0);
  }

  function renderCartPage() {
    const cartContainer = document.querySelector('#cart-items-container');
    if (!cartContainer) return;

    const cart = readCart();
    const totalEl = document.querySelector('#cart-total');
    const checkoutText = document.querySelector('#checkout-total');
    const emptyState = document.querySelector('#cart-empty-state');

    if (!cart.length) {
      cartContainer.innerHTML = '';
      if (emptyState) emptyState.classList.remove('d-none');
      if (totalEl) totalEl.textContent = formatPrice(0);
      if (checkoutText) checkoutText.textContent = formatPrice(0);
      return;
    }

    if (emptyState) emptyState.classList.add('d-none');

    cartContainer.innerHTML = cart.map((item, index) => {
      const rowTotal = item.unitPrice * item.quantity;
      return `
        <div class="cart-row" data-index="${index}">
          <div class="cart-product-cell">
            <img src="${item.image}" alt="${item.name}" class="cart-product-image">
            <div class="cart-product-details">
              <h3>${item.name}</h3>
              <p>COLOR: ${item.color}</p>
              <p>SIZE: ${item.size}</p>
            </div>
          </div>
          <div class="cart-qty-cell">
            <div class="cart-qty-box">
              <button type="button" data-action="decrease" data-index="${index}">-</button>
              <span>${item.quantity}</span>
              <button type="button" data-action="increase" data-index="${index}">+</button>
            </div>
            <button type="button" class="cart-remove" data-action="remove" data-index="${index}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <div class="cart-total-cell">${formatPrice(rowTotal)}</div>
        </div>
      `;
    }).join('');

    const grandTotal = getCartTotal(cart);
    if (totalEl) totalEl.textContent = formatPrice(grandTotal);
    if (checkoutText) checkoutText.textContent = formatPrice(grandTotal);
  }

  function initCartActions() {
    const cartContainer = document.querySelector('#cart-items-container');
    if (!cartContainer) return;

    cartContainer.addEventListener('click', (event) => {
      const target = event.target.closest('button[data-action]');
      if (!target) return;

      const action = target.getAttribute('data-action');
      const index = Number.parseInt(target.getAttribute('data-index'), 10);
      const cart = readCart();

      if (Number.isNaN(index) || !cart[index]) return;

      if (action === 'increase') {
        cart[index].quantity += 1;
      }

      if (action === 'decrease') {
        cart[index].quantity -= 1;
        if (cart[index].quantity <= 0) cart.splice(index, 1);
      }

      if (action === 'remove') {
        cart.splice(index, 1);
      }

      writeCart(cart);
      renderCartPage();
    });

    const notesToggle = document.querySelector('#toggle-notes');
    const notesArea = document.querySelector('#cart-notes-area');
    if (notesToggle && notesArea) {
      notesToggle.addEventListener('click', () => {
        notesArea.classList.toggle('d-none');
      });
    }
  }

  // Update cart badge count on all pages
  function updateCartBadge() {
    const badge = document.querySelector('.cart-badge');
    if (!badge) return;
    
    const cartItems = readCart();
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    badge.textContent = totalItems;
  }

  // Initialize functions
  function initializeApp() {
    updateCartBadge();
    initCollectionProductCards();
    initCartActions();
    renderCartPage();
    initCollectionsCarousel();
  }

  // Initialize on page load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
  } else {
    initializeApp();
  }

  /**
   * Init collections carousel
   */
  function initCollectionsCarousel() {
    const swiperElement = document.querySelector('.collectionsSwiper');
    if (!swiperElement) return;

    let currentIndex = 0;
    const slides = swiperElement.querySelectorAll('.swiper-slide');
    const totalSlides = slides.length;
    const slideWidth = 280; // max-width of collection-item
    const slideGap = 12;
    const itemWidth = slideWidth + slideGap;

    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    const scrollTopBtn = document.querySelector('.carousel-scroll-top');

    function updateCarousel() {
      const offset = currentIndex * itemWidth * -1;
      swiperElement.querySelector('.swiper-wrapper').style.transform = `translateX(${offset}px)`;
    }

    function getVisibleSlides() {
      const containerWidth = swiperElement.offsetWidth;
      return Math.floor(containerWidth / itemWidth);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        currentIndex = Math.max(0, currentIndex - 1);
        updateCarousel();
        updateButtonStates();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        const visibleSlides = getVisibleSlides();
        const maxIndex = Math.max(0, totalSlides - visibleSlides);
        currentIndex = Math.min(maxIndex, currentIndex + 1);
        updateCarousel();
        updateButtonStates();
      });
    }

    if (scrollTopBtn) {
      scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }

    function updateButtonStates() {
      const visibleSlides = getVisibleSlides();
      const maxIndex = Math.max(0, totalSlides - visibleSlides);
      
      if (prevBtn) {
        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
      }
      
      if (nextBtn) {
        nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
        nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
      }
    }

    updateCarousel();
    updateButtonStates();

    // Handle window resize
    window.addEventListener('resize', () => {
      const visibleSlides = getVisibleSlides();
      const maxIndex = Math.max(0, totalSlides - visibleSlides);
      currentIndex = Math.min(currentIndex, maxIndex);
      updateCarousel();
      updateButtonStates();
    });
  }

  /**
   * Init wishlist functionality
   */
  function initWishlist() {
    document.querySelectorAll('.wishlist-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        btn.classList.toggle('active');
        const icon = btn.querySelector('i');
        if (btn.classList.contains('active')) {
          icon.classList.remove('bi-heart');
          icon.classList.add('bi-heart-fill');
        } else {
          icon.classList.remove('bi-heart-fill');
          icon.classList.add('bi-heart');
        }
      });
    });
  }

  /**
   * Category Filter and Search/Filter Button Functionality
   */
  function initProductFiltering() {
    const categoryTabs = document.querySelectorAll('.category-tab');
    const productItems = document.querySelectorAll('.product-item');
    const filterBtn = document.getElementById('filterBtn');
    const searchBtn = document.getElementById('searchBtn');

    // Category filter
    categoryTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const selectedCategory = tab.dataset.category;
        
        // Update active tab
        categoryTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        // Filter products
        productItems.forEach(product => {
          if (selectedCategory === 'all' || product.dataset.category === selectedCategory) {
            product.style.display = '';
          } else {
            product.style.display = 'none';
          }
        });
      });
    });

    // Filter button handler
    if (filterBtn) {
      filterBtn.addEventListener('click', () => {
        console.log('Filter button clicked');
        // Add filter modal/sidebar functionality here
      });
    }

    // Search is now handled by initSearchModal()
  }

  window.addEventListener('load', initCollectionsCarousel);
  window.addEventListener('load', initProductFiltering);
  initWishlist();

  /**
   * Shopping Cart Sidebar
   */
  const cartIcon = document.querySelector('.cart-icon');
  const cartSidebar = document.getElementById('cart-sidebar');
  const cartOverlay = document.getElementById('cart-overlay');
  const cartClose = document.getElementById('cart-close');
  const cartItemsContainer = document.getElementById('cart-items');
  const cartBadgeSidebar = document.querySelector('.cart-badge');
  let sidebarCartItems = [];

  // Load cart to sidebar
  function loadSidebarCart() {
    const saved = localStorage.getItem('foa_cart_items');
    if (saved) {
      sidebarCartItems = JSON.parse(saved);
    }
    updateSidebarCart();
  }

  // Open cart sidebar
  function openCartSidebar() {
    if (cartSidebar) {
      cartSidebar.classList.add('active');
      cartOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  // Close cart sidebar
  function closeCartSidebar() {
    if (cartSidebar) {
      cartSidebar.classList.remove('active');
      cartOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  }

  // Update sidebar display
  function updateSidebarCart() {
    renderSidebarItems();
    updateSidebarTotal();
    updatePromoProgress();
  }

  // Render sidebar items
  function renderSidebarItems() {
    if (!cartItemsContainer) return;

    if (sidebarCartItems.length === 0) {
      cartItemsContainer.innerHTML = '<div class="cart-empty"><p>Your cart is empty</p></div>';
      return;
    }

    cartItemsContainer.innerHTML = sidebarCartItems.map((item, index) => `
      <div class="cart-item">
        <div class="cart-item-image">
          <img src="${item.image}" alt="${item.name}">
        </div>
        <div class="cart-item-details">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-meta">
            <span>Size: <strong>${item.size}</strong></span>
          </div>
          <div class="cart-item-bottom">
            <div class="cart-item-price">${item.unitPrice ? 'Rs ' + item.unitPrice.toFixed(2) : 'Rs 0.00'}</div>
            <button class="cart-item-remove" onclick="removeSidebaritem(${index})" title="Remove item">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  // Update sidebar total
  function updateSidebarTotal() {
    const totalEl = document.getElementById('cart-total-price');
    if (!totalEl) return;

    let total = 0;
    sidebarCartItems.forEach(item => {
      const price = item.unitPrice || 0;
      const qty = item.quantity || 1;
      total += price * qty;
    });
    totalEl.textContent = 'Rs ' + total.toFixed(2);
  }

  // Update free delivery progress bar
  function updatePromoProgress() {
    const progressBar = document.getElementById('promo-progress');
    const promoText = document.querySelector('.promo-text');
    if (!progressBar || !promoText) return;

    const FREE_DELIVERY_THRESHOLD = 15000;
    let total = 0;
    sidebarCartItems.forEach(item => {
      const price = item.unitPrice || 0;
      const qty = item.quantity || 1;
      total += price * qty;
    });

    const percentage = Math.min((total / FREE_DELIVERY_THRESHOLD) * 100, 100);
    progressBar.style.width = percentage + '%';

    const remaining = Math.max(FREE_DELIVERY_THRESHOLD - total, 0);
    if (remaining > 0) {
      promoText.innerHTML = `Spend <strong>LKR ${remaining.toFixed(2)}</strong> <span class="promo-more">more</span> for <strong>FREE delivery</strong>!`;
    } else {
      promoText.innerHTML = `<strong>You qualified for FREE delivery!</strong>`;
    }
  }

  // Remove sidebar cart item
  window.removeSidebaritem = function(index) {
    sidebarCartItems.splice(index, 1);
    localStorage.setItem('foa_cart_items', JSON.stringify(sidebarCartItems));
    updateSidebarCart();
  };

  // Event listeners
  if (cartIcon) {
    cartIcon.addEventListener('click', (e) => {
      e.preventDefault();
      openCartSidebar();
    });
  }

  if (cartClose) {
    cartClose.addEventListener('click', closeCartSidebar);
  }

  if (cartOverlay) {
    cartOverlay.addEventListener('click', closeCartSidebar);
  }

  // Load sidebar cart on page load
  window.addEventListener('load', loadSidebarCart);

  /**
   * Search Modal Functionality
   */
  function initSearchModal() {
    const searchBtn = document.getElementById('searchBtn');
    const searchModal = document.getElementById('searchModal');
    const searchModalClose = document.querySelector('.search-modal-close');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    if (!searchModal || !searchBtn) return;

    // Sample product data (in real app, this would come from an API)
    const allProducts = [
      // Women's products
      { id: 1, name: 'Classic White Shirt', category: 'women', price: 2499, image: 'assets/img/collections/women-1.jpg', color: 'White', size: 'M' },
      { id: 2, name: 'Black Blazer', category: 'women', price: 4999, image: 'assets/img/collections/women-2.jpg', color: 'Black', size: 'L' },
      { id: 3, name: 'Denim Jeans', category: 'women', price: 3299, image: 'assets/img/collections/women-3.jpg', color: 'Blue', size: 'M' },
      { id: 4, name: 'Red Dress', category: 'women', price: 5999, image: 'assets/img/collections/women-4.jpg', color: 'Red', size: 'S' },
      { id: 5, name: 'Leather Jacket', category: 'women', price: 7999, image: 'assets/img/collections/women-5.jpg', color: 'Black', size: 'M' },
      { id: 6, name: 'Floral Blouse', category: 'women', price: 2899, image: 'assets/img/collections/women-6.jpg', color: 'Multicolor', size: 'M' },
      { id: 7, name: 'Casual T-Shirt', category: 'women', price: 1599, image: 'assets/img/collections/women-7.jpg', color: 'White', size: 'L' },
      { id: 8, name: 'Cargo Pants', category: 'women', price: 3599, image: 'assets/img/collections/women-8.jpg', color: 'Khaki', size: 'M' },
      { id: 9, name: 'Silk Scarf', category: 'women', price: 1999, image: 'assets/img/collections/women-9.jpg', color: 'Purple', size: 'One' },
      { id: 10, name: 'Knit Sweater', category: 'women', price: 3899, image: 'assets/img/collections/women-10.jpg', color: 'Beige', size: 'M' },
      { id: 11, name: 'Summer Skirt', category: 'women', price: 2299, image: 'assets/img/collections/women-11.jpg', color: 'Yellow', size: 'S' },
      { id: 12, name: 'Formal Trousers', category: 'women', price: 3999, image: 'assets/img/collections/women-12.jpg', color: 'Black', size: 'M' },
      { id: 13, name: 'Hoodie Sweatshirt', category: 'women', price: 2999, image: 'assets/img/collections/women-13.jpg', color: 'Gray', size: 'L' },
      { id: 14, name: 'Linen Shirt', category: 'women', price: 2699, image: 'assets/img/collections/women-14.jpg', color: 'Cream', size: 'M' },
      { id: 15, name: 'Shorts', category: 'women', price: 1899, image: 'assets/img/collections/women-15.jpg', color: 'Denim', size: 'M' },
      { id: 16, name: 'Vest Top', category: 'women', price: 1299, image: 'assets/img/collections/women-16.jpg', color: 'Black', size: 'S' },
      { id: 17, name: 'Midi Skirt', category: 'women', price: 3299, image: 'assets/img/collections/women-17.jpg', color: 'Navy', size: 'M' },
      { id: 18, name: 'Tank Top', category: 'women', price: 999, image: 'assets/img/collections/women-18.jpg', color: 'White', size: 'M' },
      { id: 19, name: 'Long Coat', category: 'women', price: 8999, image: 'assets/img/collections/women-19.jpg', color: 'Charcoal', size: 'L' },
      { id: 20, name: 'Crop Top', category: 'women', price: 1599, image: 'assets/img/collections/women-20.jpg', color: 'Pink', size: 'S' },
      { id: 21, name: 'Maxi Dress', category: 'women', price: 6999, image: 'assets/img/collections/women-21.jpg', color: 'Black', size: 'M' },
      { id: 22, name: 'Striped Shirt', category: 'women', price: 2399, image: 'assets/img/collections/women-22.jpg', color: 'Blue/White', size: 'M' },

      // Men's products
      { id: 101, name: 'Classic Polo Shirt', category: 'men', price: 2199, image: 'assets/img/collections/men-1.jpg', color: 'Navy', size: 'M' },
      { id: 102, name: 'Formal Shirt', category: 'men', price: 2999, image: 'assets/img/collections/men-2.jpg', color: 'White', size: 'L' },
      { id: 103, name: 'Jeans', category: 'men', price: 3499, image: 'assets/img/collections/men-3.jpg', color: 'Blue', size: 'M' },
      { id: 104, name: 'Casual T-Shirt', category: 'men', price: 1299, image: 'assets/img/collections/men-4.jpg', color: 'Black', size: 'L' },
      { id: 105, name: 'Blazer', category: 'men', price: 5999, image: 'assets/img/collections/men-5.jpg', color: 'Gray', size: 'M' },
      { id: 106, name: 'Shorts', category: 'men', price: 1899, image: 'assets/img/collections/men-6.jpg', color: 'Khaki', size: 'M' },
    ];

    // Create image preview modal
    function createImagePreviewModal() {
      const existingModal = document.getElementById('imagePreviewModal');
      if (existingModal) return;

      const modal = document.createElement('div');
      modal.id = 'imagePreviewModal';
      modal.className = 'image-preview-modal';
      modal.innerHTML = `
        <div class="image-preview-content">
          <button class="image-preview-close">&times;</button>
          <img id="previewImage" src="" alt="Product Preview">
          <div class="image-preview-info">
            <h3 id="previewProductName"></h3>
            <p id="previewProductPrice"></p>
          </div>
        </div>
      `;
      document.body.appendChild(modal);

      const closeBtn = modal.querySelector('.image-preview-close');
      closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
      });

      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.remove('active');
        }
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
          modal.classList.remove('active');
        }
      });
    }

    // Show image preview
    function showImagePreview(product) {
      createImagePreviewModal();
      const modal = document.getElementById('imagePreviewModal');
      document.getElementById('previewImage').src = product.image;
      document.getElementById('previewProductName').textContent = product.name;
      document.getElementById('previewProductPrice').textContent = `Rs ${product.price}`;
      modal.classList.add('active');
    }

    // Open search modal
    function openSearchModal() {
      searchModal.classList.add('active');
      searchInput.focus();
    }

    // Close search modal
    function closeSearchModal() {
      searchModal.classList.remove('active');
      searchInput.value = '';
      searchResults.innerHTML = '';
    }

    // Search products
    function searchProducts(query) {
      const term = query.toLowerCase().trim();
      
      if (!term) {
        searchResults.innerHTML = '';
        return;
      }

      const results = allProducts.filter(product => 
        product.name.toLowerCase().includes(term) || 
        product.color.toLowerCase().includes(term) ||
        product.category.toLowerCase().includes(term)
      );

      if (results.length === 0) {
        searchResults.innerHTML = `
          <div class="search-no-results">
            <i class="bi bi-search"></i>
            <p>No products found for "<strong>${query}</strong>"</p>
          </div>
        `;
        return;
      }

      searchResults.innerHTML = results.slice(0, 8).map(product => `
        <div class="search-result-item" onclick="window.location.href='${product.category}.html#product-${product.id}'">
          <div class="search-result-details">
            <div class="search-result-name">${product.name}</div>
            <div class="search-result-meta">
              <span>${product.color}</span> • <span>Rs ${product.price}</span>
            </div>
          </div>
        </div>
      `).join('');
    }

    // Expose preview function globally
    window.searchPreviewImage = function(product) {
      showImagePreview(product);
    };

    // Event listeners
    if (searchBtn) {
      searchBtn.addEventListener('click', openSearchModal);
    }

    if (searchModalClose) {
      searchModalClose.addEventListener('click', closeSearchModal);
    }

    if (searchModal) {
      searchModal.addEventListener('click', (e) => {
        if (e.target === searchModal) {
          closeSearchModal();
        }
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        searchProducts(e.target.value);
      });

      // Allow Enter key to trigger search
      searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          const query = searchInput.value.trim();
          if (query) {
            // Redirect to women/men page for advanced search
            const firstResult = allProducts.find(p => 
              p.name.toLowerCase().includes(query.toLowerCase())
            );
            if (firstResult) {
              window.location.href = `${firstResult.category}.html`;
              closeSearchModal();
            }
          }
        }
      });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && searchModal.classList.contains('active')) {
        closeSearchModal();
      }
    });
  }

  // Initialize search modal
  window.addEventListener('load', initSearchModal);

})();