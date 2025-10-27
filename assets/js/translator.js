// English to Arabic Translation System
class Translator {
  constructor() {
    this.currentLang = localStorage.getItem("language") || "en";
    this.translations = {
      en: {
        // Navbar
        "nav-home": "Home",
        "nav-products": "Products",
        "nav-contact": "Contact",

        // Hero Section
        "hero-badge": "NextGen Digital Solutions",
        "hero-title": "Transform Your Digital Vision Into Reality",
        "hero-subtitle":
          "Unlock premium web development services, cutting-edge digital products, and expert training programs.",
        "hero-btn-explore": "Explore Products",
        "hero-btn-contact": "Get in Touch",
        "hero-web-dev": "Web Development",
        "hero-web-dev-desc": "Build stunning, responsive websites",
        "hero-training": "Expert Training",
        "hero-training-desc": "Master modern development skills",

        // Products Section
        "products-badge": "Our Collection",
        "products-title": "Featured Products & Services",
        "products-subtitle":
          "Discover our carefully crafted selection of digital solutions designed to elevate your business",
        "products-btn-all": "All Products",
        "products-btn-webdev": "Web Development",
        "products-btn-digital": "Digital Products",
        "products-btn-formations": "Formations",
        "products-btn-view": "View Details",
        "products-free-option": "Free Option",
        "products-no-products": "No Products Available",
        "products-no-products-desc": "Check back soon for new products!",

        // Categories
        "cat-webdev-services": "Web Development",
        "cat-digital-products": "Digital Products",
        "cat-formations": "Formations",

        // Product Detail
        "product-detail-breadcrumb-home": "Home",
        "product-detail-free-option": "Free Option Available",
        "product-detail-description": "Description",
        "product-detail-get-free": "Get Free Access",
        "product-detail-buy": "Buy Now",
        "product-detail-image": "Image",
        "product-detail-video": "Video",

        // Footer
        "footer-quick-links": "Quick Links",
        "footer-categories": "Categories",
        "footer-contact": "Contact Info",
        "footer-webdev": "Web Dev Services",
        "footer-digital": "Digital Products",
        "footer-formations": "Formations",
        "footer-copyright": "2025 NextGen. All rights reserved",

        // Breadcrumbs
        "breadcrumb-home": "Home",

        // Modal
        "modal-title": "Get Access",
        "modal-email": "Email Address",
        "modal-name": "Your Name",
        "modal-submit": "Submit Request",
        "modal-close": "Close",
      },
      ar: {
        // Navbar
        "nav-home": "الرئيسية",
        "nav-products": "المنتجات",
        "nav-contact": "تواصل معنا",

        // Hero Section
        "hero-badge": "حلول الجيل القادم الرقمية",
        "hero-title": "حول رؤيتك الرقمية إلى واقع",
        "hero-subtitle":
          "قم بفتح خدمات تطوير الويب المميزة، والمنتجات الرقمية المتطورة، وبرامج التدريب الاحترافية.",
        "hero-btn-explore": "استعرض المنتجات",
        "hero-btn-contact": "تواصل معنا",
        "hero-web-dev": "تطوير المواقع",
        "hero-web-dev-desc": "بناء مواقع ويب مذهلة وسريعة الاستجابة",
        "hero-training": "تدريب احترافي",
        "hero-training-desc": "إتقان مهارات التطوير الحديثة",

        // Products Section
        "products-badge": "مجموعتنا",
        "products-title": "المنتجات والخدمات المميزة",
        "products-subtitle":
          "اكتشف مجموعتنا المختارة بعناية من الحلول الرقمية المصممة لرفع مستوى أعمالك",
        "products-btn-all": "كل المنتجات",
        "products-btn-webdev": "تطوير الويب",
        "products-btn-digital": "منتجات رقمية",
        "products-btn-formations": "التدريب",
        "products-btn-view": "عرض التفاصيل",
        "products-free-option": "خيار مجاني",
        "products-no-products": "لا توجد منتجات متاحة",
        "products-no-products-desc":
          "تحقق مرة أخرى قريباً للحصول على منتجات جديدة!",

        // Categories
        "cat-webdev-services": "تطوير المواقع",
        "cat-digital-products": "منتجات رقمية",
        "cat-formations": "التدريب",

        // Product Detail
        "product-detail-breadcrumb-home": "الرئيسية",
        "product-detail-free-option": "خيار مجاني متاح",
        "product-detail-description": "الوصف",
        "product-detail-get-free": "احصل على وصول مجاني",
        "product-detail-buy": "اشتري الآن",
        "product-detail-image": "صورة",
        "product-detail-video": "فيديو",

        // Footer
        "footer-quick-links": "روابط سريعة",
        "footer-categories": "الفئات",
        "footer-contact": "معلومات الاتصال",
        "footer-webdev": "خدمات تطوير الويب",
        "footer-digital": "منتجات رقمية",
        "footer-formations": "التدريب",
        "footer-copyright": "2025 نكست جين. جميع الحقوق محفوظة",

        // Breadcrumbs
        "breadcrumb-home": "الرئيسية",

        // Modal
        "modal-title": "احصل على الوصول",
        "modal-email": "عنوان البريد الإلكتروني",
        "modal-name": "اسمك",
        "modal-submit": "إرسال الطلب",
        "modal-close": "إغلاق",
      },
    };

    this.init();
  }

  init() {
    if (this.currentLang === "ar") {
      document.documentElement.setAttribute("dir", "rtl");
      document.documentElement.setAttribute("lang", "ar");
      this.addRTLStyles();
    } else {
      document.documentElement.setAttribute("dir", "ltr");
      document.documentElement.setAttribute("lang", "en");
      this.removeRTLStyles();
    }

    this.translatePage();
  }

  translate(key) {
    return this.translations[this.currentLang][key] || key;
  }

  translatePage() {
    // Translate elements with data-translate attribute
    document.querySelectorAll("[data-translate]").forEach((element) => {
      const key = element.getAttribute("data-translate");
      const translation = this.translate(key);

      if (element.tagName === "INPUT" || element.tagName === "TEXTAREA") {
        element.setAttribute("placeholder", translation);
      } else if (element.tagName === "A" || element.tagName === "BUTTON") {
        if (element.querySelector("i")) {
          // Keep icon, translate text
          const textNodes = Array.from(element.childNodes).filter(
            (node) =>
              node.nodeType === Node.TEXT_NODE && node.textContent.trim()
          );
          if (textNodes.length > 0) {
            textNodes.forEach((node) => {
              node.textContent = " " + translation;
            });
          } else if (!element.querySelector("i").nextSibling) {
            element.appendChild(document.createTextNode(" " + translation));
          }
        } else {
          element.textContent = translation;
        }
      } else {
        element.textContent = translation;
      }
    });

    // Translate meta tags and page title if needed
    if (document.querySelector("title")) {
      const titleElement = document.querySelector("title");
      if (titleElement && titleElement.textContent.includes("NextGen")) {
        titleElement.textContent =
          this.currentLang === "ar"
            ? "نكست جين - حلول رقمية"
            : "NextGen - Digital Solutions";
      }
    }
  }

  switchLanguage(lang) {
    this.currentLang = lang;
    localStorage.setItem("language", lang);

    if (lang === "ar") {
      document.documentElement.setAttribute("dir", "rtl");
      document.documentElement.setAttribute("lang", "ar");
      this.addRTLStyles();
    } else {
      document.documentElement.setAttribute("dir", "ltr");
      document.documentElement.setAttribute("lang", "en");
      this.removeRTLStyles();
    }

    this.translatePage();
    this.updateLanguageButton();
  }

  addRTLStyles() {
    if (!document.getElementById("rtl-styles")) {
      const style = document.createElement("style");
      style.id = "rtl-styles";
      style.textContent = `
                [dir="rtl"] .navbar-nav {
                    margin-right: auto;
                    margin-left: 0;
                }
                
                [dir="rtl"] .language-toggle-btn {
                    margin-right: 15px;
                    margin-left: 0;
                }
                
                [dir="rtl"] .breadcrumb-item + .breadcrumb-item::before {
                    content: "\\0A0/\\0A0";
                    float: right;
                }
                
                [dir="rtl"] .me-2 {
                    margin-left: 0.5rem !important;
                    margin-right: 0 !important;
                }
                
                [dir="rtl"] .ms-auto {
                    margin-right: auto !important;
                    margin-left: 0 !important;
                }
            `;
      document.head.appendChild(style);
    }
  }

  removeRTLStyles() {
    const rtlStyle = document.getElementById("rtl-styles");
    if (rtlStyle) {
      rtlStyle.remove();
    }
  }

  updateLanguageButton() {
    const langBtn = document.getElementById("langBtn");
    if (langBtn) {
      if (this.currentLang === "ar") {
        langBtn.innerHTML = '<i class="fas fa-language me-2"></i>English';
      } else {
        langBtn.innerHTML = '<i class="fas fa-language me-2"></i>العربية';
      }
    }
  }

  getCurrentLang() {
    return this.currentLang;
  }
}

// Initialize translator
const translator = new Translator();

// Language button handler
function toggleLanguage() {
  const currentLang = translator.getCurrentLang();
  translator.switchLanguage(currentLang === "en" ? "ar" : "en");
}

// Export for use in other scripts
window.translator = translator;
window.toggleLanguage = toggleLanguage;
