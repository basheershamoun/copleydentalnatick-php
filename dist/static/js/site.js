// Copley Dental Site JavaScript
// Centralized functionality for navbar, forms, and other interactive elements

(function() {
  'use strict';

  // === NAVBAR FUNCTIONALITY ===
  function initNavbar() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.body;

    if (mobileMenuButton && mobileMenu) {
      // Toggle mobile menu
      mobileMenuButton.addEventListener('click', function() {
        const isOpen = mobileMenu.classList.contains('translate-x-0');
        
        if (isOpen) {
          mobileMenu.classList.remove('translate-x-0');
          mobileMenu.classList.add('translate-x-full');
          body.style.overflow = '';
        } else {
          mobileMenu.classList.remove('translate-x-full');
          mobileMenu.classList.add('translate-x-0');
          body.style.overflow = 'hidden';
        }
      });

      // Handle dropdown toggles in mobile menu
      const dropdownToggles = mobileMenu.querySelectorAll('[data-dropdown-toggle]');
      dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
          e.preventDefault();
          const dropdownId = toggle.getAttribute('data-dropdown-toggle');
          const dropdown = document.getElementById(dropdownId);
          
          if (dropdown) {
            const icon = toggle.querySelector('svg');
            const isOpen = !dropdown.classList.contains('hidden');
            
            if (isOpen) {
              dropdown.classList.add('hidden');
              if (icon) icon.classList.remove('rotate-180');
            } else {
              dropdown.classList.remove('hidden');
              if (icon) icon.classList.add('rotate-180');
            }
          }
        });
      });

      // Close menu when clicking outside
      document.addEventListener('click', function(e) {
        if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
          if (mobileMenu.classList.contains('translate-x-0')) {
            mobileMenu.classList.remove('translate-x-0');
            mobileMenu.classList.add('translate-x-full');
            body.style.overflow = '';
          }
        }
      });

      // Close menu on escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('translate-x-0')) {
          mobileMenu.classList.remove('translate-x-0');
          mobileMenu.classList.add('translate-x-full');
          body.style.overflow = '';
        }
      });
    }

    // Desktop dropdowns
    const desktopDropdowns = document.querySelectorAll('.group');
    desktopDropdowns.forEach(function(dropdown) {
      const menu = dropdown.querySelector('.group-hover\\:block');
      if (menu) {
        // Keyboard accessibility
        dropdown.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            menu.classList.toggle('block');
          }
        });
      }
    });
  }

  // === FORM FUNCTIONALITY ===
  function initForms() {
    const forms = document.querySelectorAll('.contact-form');
    
    forms.forEach(function(form) {
      const formId = form.id.replace('form-', '');
      const statusMessage = document.getElementById(`status-${formId}`);
      const formContainer = document.getElementById(`form-container-${formId}`);
      
      if (!form || !statusMessage) return;

      // Get redirect URL from data attribute or default
      const redirectTo = formContainer?.dataset.redirectTo || '/thank-you-page';

      // Clear error on field interaction
      form.querySelectorAll('.form-input').forEach(function(input) {
        function clearErrorStyle() {
          input.style.removeProperty('border');
          input.style.removeProperty('box-shadow');
          input.style.removeProperty('outline');
        }
        input.addEventListener('focus', clearErrorStyle);
        input.addEventListener('input', clearErrorStyle);
      });

      // Form submission handler
      form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Show validating status
        showStatus(statusMessage, 'Validating...', 'warning');

        // Validate form
        const validation = validateForm(form);

        if (!validation.isValid) {
          showStatus(statusMessage, 'Please fill in all required fields correctly', 'error');

          // Scroll to first invalid field so user can see the error
          if (validation.firstInvalidField) {
            validation.firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            validation.firstInvalidField.focus();
          } else {
            // Fallback: scroll to status message
            statusMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          return;
        }

        // Show sending status
        showStatus(statusMessage, 'Sending message...', 'warning');

        // Prepare form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Handle name fields - split fullName into firstName/lastName if needed
        if (data.firstName && data.lastName) {
          data.fullName = `${data.firstName} ${data.lastName}`;
        } else if (data.fullName) {
          // Split fullName into firstName and lastName for API
          const nameParts = data.fullName.trim().split(' ');
          data.firstName = nameParts[0] || '';
          data.lastName = nameParts.slice(1).join(' ') || '';
        } else {
          data.fullName = data.name || '';
          data.firstName = data.firstName || '';
          data.lastName = data.lastName || '';
        }

        // Prepare API data — prepend site location (e.g. "Natick: ") to firstName + fullName
        // so the CRM notification (e.g. Telegram) shows which site the submission came from.
        const loc = (typeof window !== 'undefined' && window.SITE_LOCATION) ? String(window.SITE_LOCATION).trim() : '';
        const prefix = loc ? `${loc}: ` : '';
        const apiFormData = new FormData();
        apiFormData.append('firstName', prefix + (data.firstName || ''));
        apiFormData.append('lastName', data.lastName || '');
        apiFormData.append('fullName', prefix + (data.fullName || ''));
        apiFormData.append('mobile', data.phone || '');
        apiFormData.append('email', (data.email || '').toLowerCase());
        apiFormData.append('message', formatMessage(data));
        apiFormData.append('pageUrl', window.location.href);

        try {
          const randomId = Math.floor(Math.random() * (99998 - 10000 + 1) + 10000);
          const response = await fetch(`https://copleydentalforms.arzs.app/api/contact/${randomId}`, {
            method: 'POST',
            body: apiFormData
          });

          if (response.ok) {
            showStatus(statusMessage, 'Message sent successfully!', 'success');
            form.reset();
            
            // Track conversion with GTM if available
            if (typeof dataLayer !== 'undefined') {
              dataLayer.push({
                'event': 'form_submission',
                'form_id': formId,
                'form_name': form.dataset.formName || 'contact_form'
              });
            }

            // Redirect after success
            setTimeout(function() {
              window.location.href = redirectTo;
            }, 1000);
          } else {
            throw new Error('Failed to send message');
          }
        } catch (error) {
          console.error('Form submission error:', error);
          showStatus(statusMessage, 'Something went wrong. Please try again.', 'error');
        }
      });
    });
  }

  // === HELPER FUNCTIONS ===
  
  function validateForm(form) {
    let isValid = true;
    const errors = [];
    let firstInvalidField = null;

    // Helper to highlight field with error
    function highlightError(field) {
      field.style.setProperty('border', '2px solid #ef4444', 'important');
      field.style.setProperty('box-shadow', '0 0 0 3px rgba(239, 68, 68, 0.3)', 'important');
      field.style.setProperty('outline', 'none', 'important');

      // Track first invalid field for scrolling
      if (!firstInvalidField) {
        firstInvalidField = field;
      }
    }

    // Validate required fields
    const requiredFields = form.querySelectorAll('[required], .not-empty');
    requiredFields.forEach(function(field) {
      // Skip checkboxes and radio buttons for length validation
      if (field.type === 'checkbox' || field.type === 'radio') {
        return;
      }

      const value = field.value.trim();

      if (value.length < 3) {
        highlightError(field);
        isValid = false;
        errors.push(field.name || field.placeholder);
      }
    });

    // Validate email fields
    const emailFields = form.querySelectorAll('[type="email"], .valid-mail');
    emailFields.forEach(function(field) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (field.value && !emailRegex.test(field.value.toLowerCase())) {
        highlightError(field);
        isValid = false;
        errors.push('Invalid email');
      }
    });

    // Validate phone fields - must have at least 7 digits
    const phoneFields = form.querySelectorAll('[type="tel"]');
    phoneFields.forEach(function(field) {
      const value = field.value.trim();
      const digitsOnly = value.replace(/\D/g, '');

      // Phone must contain only valid characters AND have at least 7 digits
      const phoneRegex = /^[\d\s\-\+\(\)\.]+$/;
      if (value && (!phoneRegex.test(value) || digitsOnly.length < 7)) {
        highlightError(field);
        isValid = false;
        errors.push('Invalid phone number');
      }
    });

    return { isValid, errors, firstInvalidField };
  }

  function formatMessage(data) {
    let message = data.message || '';

    // Prepend site location (e.g. "Natick: ") when window.SITE_LOCATION is set
    const location = (typeof window !== 'undefined' && window.SITE_LOCATION) ? String(window.SITE_LOCATION).trim() : '';
    if (location) {
      message = `${location}: ` + (message || '').replace(/^\s+/, '');
    }

    // Core fields that shouldn't be concatenated (already sent separately to API)
    const coreFields = ['firstName', 'lastName', 'fullName', 'name', 'email', 'phone', 'mobile', 'message'];

    // Generic concatenation of all additional fields
    Object.keys(data).forEach(function(key) {
      const value = data[key];

      // Skip core fields, empty values, and "not-selected" defaults
      if (coreFields.includes(key) || !value || value === '' || value === 'not-selected') {
        return;
      }

      // Convert camelCase to readable format (e.g., teethMissing -> Teeth Missing)
      const label = key
        .replace(/([A-Z])/g, ' $1') // Add space before capital letters
        .replace(/^./, function(str) { return str.toUpperCase(); }) // Capitalize first letter
        .trim();

      // Add to message
      if (message && !message.endsWith('\n\n')) {
        message += message ? '\n' : '\n\n';
      }
      message += `${label}: ${value}`;
    });

    return message;
  }

  function showStatus(element, message, type) {
    if (!element) return;

    const statusClasses = {
      success: 'text-green-600 bg-green-50 border-green-600',
      error: 'text-red-600 bg-red-50 border-red-600',
      warning: 'text-orange-600 bg-orange-50 border-orange-600',
      info: 'text-blue-600 bg-blue-50 border-blue-600'
    };

    element.textContent = message;
    element.className = 'block w-full box-border px-5 py-3 rounded-lg text-center mb-4 border ' + (statusClasses[type] || statusClasses.info);
  }

  // === SMOOTH SCROLL ===
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        const href = anchor.getAttribute('href');
        if (href && href !== '#' && href !== '#0') {
          const target = document.querySelector(href);
          if (target) {
            e.preventDefault();
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        }
      });
    });
  }

  // === ACCORDION FUNCTIONALITY ===
  function initAccordions() {
    const accordions = document.querySelectorAll('[data-accordion]');
    
    accordions.forEach(function(accordion) {
      const toggle = accordion.querySelector('[data-accordion-toggle]');
      const content = accordion.querySelector('[data-accordion-content]');
      
      if (toggle && content) {
        toggle.addEventListener('click', function() {
          const isOpen = !content.classList.contains('hidden');
          
          if (isOpen) {
            content.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
          } else {
            content.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
          }
          
          // Rotate icon if present
          const icon = toggle.querySelector('[data-accordion-icon]');
          if (icon) {
            icon.classList.toggle('rotate-180');
          }
        });
      }
    });
  }

  // === INITIALIZE ON DOM READY ===
  function init() {
    initNavbar();
    initForms();
    initSmoothScroll();
    initAccordions();
  }

  // Run initialization when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-initialize after Astro page transitions (if using View Transitions)
  document.addEventListener('astro:after-swap', init);

})();