document.addEventListener('DOMContentLoaded', function () {
  console.log('main.js: DOMContentLoaded fired');

  
  const searchForm = document.querySelector('.search-form');
  const searchBtn = document.querySelector('#search-btn');
  searchBtn.addEventListener('click', () => {
      const isActive = searchForm.classList.toggle('active');
      searchForm.setAttribute('aria-expanded', isActive);
      if (isActive) {
          document.querySelector('#search-box').focus();
      }
  });

  
  window.addEventListener('scroll', () => {
      searchForm.classList.remove('active');
      searchForm.setAttribute('aria-expanded', 'false');
      const header2 = document.querySelector('.header .header-2');
      if (window.scrollY > 80) {
          header2.classList.add('active');
      } else {
          header2.classList.remove('active');
      }
  });

  
  try {
      new Swiper('.books-slider', {
          loop: true,
          autoplay: { delay: 3000, disableOnInteraction: false },
          slidesPerView: 1,
          spaceBetween: 10,
          a11y: {
              enabled: true,
              prevSlideMessage: 'Previous book',
              nextSlideMessage: 'Next book',
              firstSlideMessage: 'First book',
              lastSlideMessage: 'Last book'
          },
          breakpoints: {
              768: { slidesPerView: 2 },
              1024: { slidesPerView: 3 }
          }
      });

      const featuredSwiper = new Swiper('.featured-slider', {
        loop: true,
        autoplay: { delay: 2500, disableOnInteraction: false },
        slidesPerView: 3,
        spaceBetween: 10,
        navigation: { nextEl: '.featured-slider .swiper-button-next', prevEl: '.featured-slider .swiper-button-prev' },
        a11y: {
            enabled: true,
            prevSlideMessage: 'Previous book',
            nextSlideMessage: 'Next book',
            firstSlideMessage: 'First book',
            lastSlideMessage: 'Last book'
        },
        breakpoints: {
            0: { slidesPerView: 1, spaceBetween: 5 },
            768: { slidesPerView: 2, spaceBetween: 10 },
            1024: { slidesPerView: 3, spaceBetween: 10 }
        },
        loopAdditionalSlides: 2,
        on: {
            init: function () {
                console.log('main.js: Featured slider initialized, slidesPerView:', this.params.slidesPerView, 'slides:', this.slides.length);
                console.log('main.js: Slider width:', this.el.offsetWidth);
            },
            slidesLengthChange: function () {
                console.log('main.js: Slides changed, count:', this.slides.length);
                if (this.slides.length < 4) {
                    console.warn('main.js: <4 slides, loop may fail');
                }
            }
        }
    });

      new Swiper('.arrivals-slider', {
          loop: true,
          centeredSlides: true,
          autoplay: { delay: 9500, disableOnInteraction: false },
          a11y: {
              enabled: true,
              prevSlideMessage: 'Previous arrival',
              nextSlideMessage: 'Next arrival'
          },
          breakpoints: {
              0: { slidesPerView: 1 },
              768: { slidesPerView: 2 },
              1024: { slidesPerView: 3 }
          }
      });

      new Swiper('.reviews-slider', {
          loop: true,
          grabCursor: true,
          spaceBetween: 30,
          centeredSlides: true,
          autoplay: { delay: 3000, disableOnInteraction: false },
          pagination: { el: '.swiper-pagination', clickable: true },
          a11y: {
              enabled: true,
              paginationBulletMessage: 'Go to review {{index}}',
              prevSlideMessage: 'Previous review',
              nextSlideMessage: 'Next review'
          },
          breakpoints: {
              0: { slidesPerView: 1 },
              768: { slidesPerView: 2 },
              991: { slidesPerView: 3 }
          }
      });

      new Swiper('.blogs-slider', {
          loop: true,
          grabCursor: true,
          spaceBetween: 20,
          centeredSlides: true,
          autoplay: { delay: 4000, disableOnInteraction: false },
          pagination: { el: '.swiper-pagination', clickable: true },
          navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
          a11y: {
              enabled: true,
              paginationBulletMessage: 'Go to blog {{index}}',
              prevSlideMessage: 'Previous blog',
              nextSlideMessage: 'Next blog'
          },
          breakpoints: {
              0: { slidesPerView: 1 },
              768: { slidesPerView: 2 },
              1024: { slidesPerView: 3 }
          }
      });

      console.log('main.js: Swiper sliders initialized with a11y');
  } catch (error) {
      console.error('main.js: Swiper initialization error:', error);
  }

  // Scroll animations
  function isElementInView(element) {
      const rect = element.getBoundingClientRect();
      return rect.top < window.innerHeight && rect.bottom >= 0;
  }

  function handleScroll() {
      const elements = document.querySelectorAll('.animated-element');
      elements.forEach(element => {
          if (isElementInView(element)) {
              element.classList.add('visible');
          }
      });
  }

  window.addEventListener('scroll', handleScroll);
  handleScroll();


  async function checkSession() {
      try {
          const response = await fetch('check_session.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              credentials: 'include'
          });
          console.log('main.js: check_session.php status:', response.status);
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          const data = await response.json();
          console.log('main.js: check_session.php response:', data);
          return data.isLoggedIn ? data.userId : 0;
      } catch (error) {
          console.error('main.js: Error checking session:', error);
          return 0;
      }
  }

  
  document.querySelectorAll('.fa-heart').forEach((icon) => {
      icon.addEventListener('click', async (event) => {
          event.preventDefault();
          const isWishlistBtn = icon.id === 'wishlist-btn';
          const bookId = icon.getAttribute('data-book-id');
          const bookTitle = icon.getAttribute('data-title') || 'Book';

          console.log('main.js: Heart icon clicked', { isWishlistBtn, bookId, bookTitle });

          const sessionUserId = await checkSession();
          if (sessionUserId === 0) {
              showNotification('Please log in to access wishlist.', 'error');
              setTimeout(() => window.location.href = 'login.html', 1000);
              return;
          }

          if (isWishlistBtn && !bookId) {
              console.log('main.js: Redirecting to wishlist.php (no book context)');
              window.location.href = 'wishlist.php';
              return;
          }

          if (!bookId) {
              showNotification('Invalid book ID.', 'error');
              return;
          }

          if (icon.classList.contains('disabled')) {
              showNotification('Book already in wishlist.', 'error');
              if (isWishlistBtn) {
                  window.location.href = 'wishlist.php';
              }
              return;
          }

          console.log('main.js: Saving to wishlist, user_id:', sessionUserId, ', book_id:', bookId);
          fetch('save_to_wishlist.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ user_id: sessionUserId, book_id: String(bookId) })
          })
          .then(response => {
              console.log('main.js: save_to_wishlist.php status:', response.status);
              if (!response.ok) {
                  return response.text().then(text => {
                      throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                  });
              }
              return response.json();
          })
          .then(data => {
              console.log('main.js: save_to_wishlist.php response:', data);
              if (data.success) {
                  showNotification(`"${bookTitle}" added to wishlist!`, 'success');
                  icon.classList.add('active-heart', 'disabled');
                  icon.setAttribute('aria-label', `${bookTitle} added to wishlist`);
              } else {
                  showNotification('Error: ' + data.message, 'error');
              }
          })
          .catch(error => {
              console.error('main.js: Wishlist AJAX error:', error);
              showNotification('Failed to save to wishlist: ' + error.message, 'error');
          });
      });
  });

  
  document.querySelectorAll('.save-to-wishlist').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
          e.preventDefault();
          if (btn.classList.contains('disabled')) {
              showNotification('Book already in wishlist.', 'error');
              return;
          }
          const bookId = btn.getAttribute('data-book-id');
          const bookTitle = btn.getAttribute('data-title') || 'Book';

          console.log('main.js: Checking session before wishlist action (save-to-wishlist), book_id:', bookId);
          const sessionUserId = await checkSession();
          if (sessionUserId === 0) {
              showNotification('Please log in to save to wishlist.', 'error');
              setTimeout(() => window.location.href = 'login.html', 1000);
              return;
          }

          console.log('main.js: Saving to wishlist, user_id:', sessionUserId, ', book_id:', bookId);
          fetch('save_to_wishlist.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ user_id: sessionUserId, book_id: String(bookId) })
          })
          .then(response => {
              console.log('main.js: save_to_wishlist.php status:', response.status);
              if (!response.ok) {
                  return response.text().then(text => {
                      throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                  });
              }
              return response.json();
          })
          .then(data => {
              console.log('main.js: save_to_wishlist.php response:', data);
              if (data.success) {
                  btn.innerHTML = '<i class="fas fa-heart" aria-hidden="true"></i> In Wishlist';
                  btn.classList.add('active', 'disabled');
                  btn.setAttribute('aria-label', `${bookTitle} added to wishlist`);
                  showNotification(`"${bookTitle}" added to wishlist!`, 'success');
              } else {
                  showNotification('Error: ' + data.message, 'error');
              }
          })
          .catch(error => {
              console.error('main.js: Wishlist AJAX error:', error);
              showNotification('Failed to save to wishlist: ' + error.message, 'error');
          });
      });
  });

  
  const featuredWrapper = document.getElementById("featured-books-wrapper");
  const arrivalsWrapper = document.querySelector(".arrivals-slider .swiper-wrapper");
  const userId = window.userId || 0;


  const fallbackBooks = [
      { book_id: 'fallback1', title: 'Sample Book 1', author: 'Author 1', price: '10.00', stock: 50, image_url: 'images/new-book1.avif' },
      { book_id: 'fallback2', title: 'Sample Book 2', author: 'Author 2', price: '12.00', stock: 50, image_url: 'images/new-book2.jpg' }
  ];

  
  const cacheTime = localStorage.getItem('featuredBooksTime');
  if (cacheTime && Date.now() - cacheTime > 24 * 60 * 60 * 1000) {
      localStorage.removeItem('featuredBooks');
      localStorage.removeItem('featuredBooksTime');
  }

  
  const cachedBooks = localStorage.getItem('featuredBooks');
  let featuredBooks = cachedBooks ? JSON.parse(cachedBooks) : [];

  function renderBooks(wrapper, books, sliderClass, isFeatured = true) {
      try {
          wrapper.innerHTML = '';
          wrapper.setAttribute('aria-busy', 'true');
          books.forEach((book) => {
              const { book_id, title, price, image_url } = book;
              const oldPrice = (parseFloat(price) + 5).toFixed(2);
              const slide = document.createElement("div");
              slide.className = "swiper-slide box";
              slide.dataset.bookId = book_id;
              slide.setAttribute('role', 'group');
              slide.setAttribute('aria-label', `${isFeatured ? 'Featured' : 'New arrival'} book: ${title}`);
              slide.innerHTML = `
                  <div class="icons">
                      <a href="#" class="fas fa-search" title="Quick View" data-title="${title}" data-thumbnail="${image_url}" data-price="${price}" aria-label="Quick view for ${title}"></a>
                      <a href="book_details.php?book_id=${encodeURIComponent(book_id)}" class="fas fa-eye" title="View Details" aria-label="View details for ${title}"></a>
                  </div>
                  <div class="image">
                      <img src="${image_url}" alt="Book cover for ${title}">
                  </div>
                  <div class="content">
                      <h3>${title}</h3>
                      <div class="price">$${price} <span>$${oldPrice}</span></div>
                      <a href="#" class="btn add-to-cart" data-book-id="${book_id}" data-title="${title}" data-price="${price}" aria-label="Add ${title} to cart">add to cart</a>
                  </div>
              `;
              wrapper.appendChild(slide);
          });

          new Swiper(`.${sliderClass}`, {
              loop: true,
              spaceBetween: 20,
              navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
              a11y: {
                  enabled: true,
                  prevSlideMessage: `Previous ${isFeatured ? 'featured' : 'new arrival'} book`,
                  nextSlideMessage: `Next ${isFeatured ? 'featured' : 'new arrival'} book`
              },
              breakpoints: {
                  640: { slidesPerView: 1 },
                  768: { slidesPerView: 2 },
                  1024: { slidesPerView: 3 }
              }
          });

          attachCartListeners();
          attachSearchListeners();
          wrapper.setAttribute('aria-busy', 'false');
          console.log(`main.js: ${isFeatured ? 'Featured' : 'Arrivals'} books rendered successfully`);
      } catch (error) {
          console.error(`main.js: Error rendering ${isFeatured ? 'featured' : 'arrivals'} books:`, error);
          wrapper.setAttribute('aria-busy', 'false');
      }
  }

  function attachCartListeners() {
      console.log('main.js: Attaching cart listeners');
      document.querySelectorAll('.add-to-cart').forEach(button => {
          button.removeEventListener('click', handleCartClick);
          button.addEventListener('click', handleCartClick);
      });
  }

  function handleCartClick(e) {
      e.preventDefault();
      const button = e.currentTarget;
      const bookId = button.getAttribute('data-book-id');
      const bookTitle = button.getAttribute('data-title');
      console.log('main.js: Add to cart clicked:', { bookId, userId, bookIdType: typeof bookId, userIdType: typeof userId });

      if (userId === 0) {
          showNotification('Please log in to continue.', 'error');
          setTimeout(() => window.location.href = 'login.html', 1000);
          return;
      }

      if (!bookId) {
          showNotification('Invalid book ID.', 'error');
          return;
      }

      addToCart(bookId, button, bookTitle);
  }

  function addToCart(bookId, button, bookTitle) {
      const data = {
          book_id: String(bookId),
          user_id: Number(userId)
      };

      button.setAttribute('aria-busy', 'true');
      fetch('add_to_cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
      })
      .then(response => {
          console.log('main.js: add_to_cart.php status:', response.status);
          if (!response.ok) {
              return response.text().then(text => {
                  throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
              });
          }
          return response.text();
      })
      .then(text => {
          try {
              const data = JSON.parse(text);
              if (data.success) {
                  showNotification(`"${bookTitle}" added to cart!`, 'success');
                  updateCartCount();
                  document.dispatchEvent(new Event('cart-updated'));
                  if (data.new_stock === 0) {
                      button.style.display = 'none';
                      const buttons = document.querySelectorAll(`.add-to-cart[data-book-id="${bookId}"]`);
                      buttons.forEach(btn => {
                          btn.style.display = 'none';
                          btn.insertAdjacentHTML('afterend', '<p class="out-of-stock" aria-live="polite">Out of Stock</p>');
                      });
                  }
              } else {
                  showNotification(data.message || 'Error adding to cart.', 'error');
              }
          } catch (e) {
              console.error('main.js: JSON parse error:', e, 'Raw response:', text);
              showNotification('Error adding to cart: Invalid server response.', 'error');
          }
          button.setAttribute('aria-busy', 'false');
      })
      .catch(error => {
          console.error('main.js: Error adding to cart:', error);
          showNotification('Error adding to cart: ' + error.message, 'error');
          button.setAttribute('aria-busy', 'false');
      });
  }

  function attachSearchListeners() {
      document.querySelectorAll(".fa-search").forEach((icon) => {
          icon.removeEventListener('click', handleSearchClick);
          icon.addEventListener('click', handleSearchClick);
      });
  }

  function handleSearchClick(event) {
      event.preventDefault();
      const title = event.currentTarget.dataset.title;
      const thumbnail = event.currentTarget.dataset.thumbnail;
      const price = event.currentTarget.dataset.price;
      showQuickView(title, thumbnail, price);
  }

  
  if (featuredWrapper) {
      
      try {
          if (featuredBooks.length > 0) {
              console.log('main.js: Rendering cached featured books');
              renderBooks(featuredWrapper, featuredBooks, 'featured-slider', true);
          } else {
              console.log('main.js: Rendering fallback featured books');
              featuredBooks = fallbackBooks;
              renderBooks(featuredWrapper, featuredBooks, 'featured-slider', true);
          }
      } catch (error) {
          console.error('main.js: Error rendering initial featured books:', error);
      }

    
      fetch('fetch_books2.php?type=featured', {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' }
      })
      .then(response => {
          console.log('main.js: fetch_books2.php?type=featured status:', response.status);
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          console.log('main.js: fetch_books2.php?type=featured response:', data);
          if (data.success && data.books.length > 0) {
              featuredBooks = data.books;
            
              localStorage.setItem('featuredBooks', JSON.stringify(featuredBooks));
              localStorage.setItem('featuredBooksTime', Date.now());
              renderBooks(featuredWrapper, featuredBooks, 'featured-slider', true);
          } else {
              console.warn('main.js: No featured books from database, using fallback');
              featuredBooks = fallbackBooks;
              renderBooks(featuredWrapper, featuredBooks, 'featured-slider', true);
          }
      })
      .catch(error => {
          console.error('main.js: Error fetching featured books:', error);
          if (!featuredBooks.length) {
              featuredBooks = fallbackBooks;
              renderBooks(featuredWrapper, featuredBooks, 'featured-slider', true);
          }
          showNotification('Failed to load featured books.', 'error');
      });
  }

  if (arrivalsWrapper) {
      
      try {
          console.log('main.js: Rendering fallback arrivals books');
          renderBooks(arrivalsWrapper, fallbackBooks, 'arrivals-slider', false);
      } catch (error) {
          console.error('main.js: Error rendering initial arrivals books:', error);
      }

      
      fetch('fetch_books2.php?type=arrivals', {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' }
      })
      .then(response => {
          console.log('main.js: fetch_books2.php?type=arrivals status:', response.status);
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          console.log('main.js: fetch_books2.php?type=arrivals response:', data);
          if (data.success && data.books.length > 0) {
              renderBooks(arrivalsWrapper, data.books, 'arrivals-slider', false);
          } else {
              console.warn('main.js: No arrivals books from database, using fallback');
              renderBooks(arrivalsWrapper, fallbackBooks, 'arrivals-slider', false);
          }
      })
      .catch(error => {
          console.error('main.js: Error fetching arrivals books:', error);
          renderBooks(arrivalsWrapper, fallbackBooks, 'arrivals-slider', false);
          showNotification('Failed to load new arrivals.', 'error');
      });
  }

  
  const style = document.createElement('style');
  style.innerHTML = `
      .fa-heart.active-heart {
          color: #ff758f;
      }
      .fa-heart.disabled {
          pointer-events: none;
          opacity: 0.7;
      }
      .quick-view-modal {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000;
      }
      .quick-view-content {
          background: #fff;
          padding: 20px;
          border-radius: 5px;
          max-width: 500px;
          position: relative;
      }
      .close-modal {
          position: absolute;
          top: 10px;
          right: 10px;
          cursor: pointer;
          font-size: 20px;
      }
      .notification[role="alert"] {
          position: fixed;
          top: 20px;
          right: 20px;
          padding: 10px 20px;
          border-radius: 5px;
          z-index: 1000;
          transition: opacity 0.5s;
      }
      .notification.success {
          background: #28a745;
          color: #fff;
      }
      .notification.error {
          background: #dc3545;
          color: #fff;
      }
  `;
  document.head.appendChild(style);

  function showQuickView(title, thumbnail, price) {
      const modal = document.createElement("div");
      modal.className = "quick-view-modal";
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-labelledby', 'quick-view-title');
      modal.setAttribute('aria-modal', 'true');
      modal.innerHTML = `
          <div class="quick-view-content">
              <button class="close-modal" aria-label="Close quick view">×</button>
              <h3 id="quick-view-title">${title}</h3>
              <img src="${thumbnail}" alt="Book cover for ${title}" style="width: 100%; max-height: 300px; object-fit: contain;">
              <p>Price: $${price}</p>
          </div>
      `;
      document.body.appendChild(modal);

    
      const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      const firstFocusable = focusableElements[0];
      const lastFocusable = focusableElements[focusableElements.length - 1];

      firstFocusable.focus();

      function trapFocus(e) {
          if (e.key === 'Tab') {
              if (e.shiftKey) {
                  if (document.activeElement === firstFocusable) {
                      e.preventDefault();
                      lastFocusable.focus();
                  }
              } else {
                  if (document.activeElement === lastFocusable) {
                      e.preventDefault();
                      firstFocusable.focus();
                  }
              }
          }
          if (e.key === 'Escape') {
              modal.remove();
          }
      }

      modal.addEventListener('keydown', trapFocus);

      const closeButton = modal.querySelector(".close-modal");
      closeButton.addEventListener("click", () => {
          modal.remove();
      });

      window.addEventListener("click", (event) => {
          if (event.target === modal) {
              modal.remove();
          }
      });
  }

  function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.setAttribute('role', 'alert');
      notification.setAttribute('aria-live', 'assertive');
      notification.textContent = message;
      document.body.appendChild(notification);
      setTimeout(() => {
          notification.style.opacity = '0';
          setTimeout(() => notification.remove(), 500);
      }, 3000);
  }

  function updateCartCount(maxRetries = 3, retryDelay = 1000) {
      const cartCount = document.querySelector('.cart-count');
      if (!cartCount) {
          console.error('main.js: Cart count element not found');
          return;
      }
      const userId = window.userId || 0;
      const initialCount = parseInt(cartCount.textContent) || 0;
      const cartBtn = document.querySelector('#cart-btn');

      function attemptFetch(retryCount) {
          fetch('get_cart_count.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ user_id: userId })
          })
          .then(response => {
              console.log('main.js: get_cart_count.php status:', response.status);
              if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
          })
          .then(data => {
              cartCount.textContent = data.cart_count || 0;
              cartCount.classList.add('updated');
              cartBtn.setAttribute('aria-label', `View cart with ${data.cart_count || 0} items`);
              setTimeout(() => cartCount.classList.remove('updated'), 300);
              console.log('main.js: Cart count updated:', data.cart_count);
          })
          .catch(error => {
              console.error(`main.js: Cart count fetch attempt ${maxRetries - retryCount + 1} failed:`, error);
              if (retryCount > 0) {
                  setTimeout(() => attemptFetch(retryCount - 1), retryDelay);
              } else {
                  cartCount.textContent = initialCount;
                  cartBtn.setAttribute('aria-label', `View cart with ${initialCount} items`);
                  showNotification('Failed to update cart count.', 'error');
              }
          });
      }
      attemptFetch(maxRetries);
  }
});

document.getElementById('newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('newsletter-email');
    const messageBox = document.getElementById('message');

    messageBox.setAttribute('aria-busy', 'true');
    fetch('subscribe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `email=${encodeURIComponent(email.value)}`
    })
    .then(response => response.text())
    .then(data => {
        messageBox.textContent = data;
        messageBox.style.display = "block";
        messageBox.style.opacity = 1;

        if (data.toLowerCase().includes("thank you")) {
            messageBox.className = "success";
            messageBox.setAttribute('aria-label', 'Subscription successful');
        } else {
            messageBox.className = "error";
            messageBox.setAttribute('aria-label', 'Subscription error');
        }

        email.value = "";
        messageBox.setAttribute('aria-busy', 'false');

        setTimeout(() => {
            messageBox.style.opacity = 0;
            setTimeout(() => {
                messageBox.style.display = "none";
                messageBox.className = "";
                messageBox.removeAttribute('aria-label');
            }, 1000);
        }, 5000);
    })
    .catch(error => {
        messageBox.textContent = "Something went wrong.";
        messageBox.style.display = "block";
        messageBox.style.opacity = 1;
        messageBox.className = "error";
        messageBox.setAttribute('aria-label', 'Subscription error');
        messageBox.setAttribute('aria-busy', 'false');

        setTimeout(() => {
            messageBox.style.opacity = 0;
            setTimeout(() => {
                messageBox.style.display = "none";
                messageBox.className = "";
                messageBox.removeAttribute('aria-label');
            }, 1000);
        }, 5000);
    });
});


  

  
    const toggleBtn = document.getElementById('pagesToggle');
    const pagesMenu = document.getElementById('pagesMenu');

    
    toggleBtn.addEventListener('click', () => {
        const isVisible = pagesMenu.style.display === 'flex';
        pagesMenu.style.display = isVisible ? 'none' : 'flex';
        pagesMenu.setAttribute('aria-hidden', isVisible ? 'true' : 'false');
    });

  
    const menuLinks = pagesMenu.querySelectorAll('a');

    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            pagesMenu.style.display = 'none';
            pagesMenu.setAttribute('aria-hidden', 'true');
        });
    });



    
    const navLinks = document.querySelectorAll('.bottom-navbar a');

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });



