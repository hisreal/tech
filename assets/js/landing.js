/* ==========================================================================
   Public Landing Page — interactions
   ========================================================================== */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    initLoader();
    initAOS();
    initAosSafetyNet();
    initScrollProgress();
    initStickyNav();
    initMobileMenu();
    initSmoothScroll();
    initBackToTop();
    initTyped();
    initShowcaseTabs();
    initSwiper();
    initCounters();
    initDemoForm();
    initRipple();
    initNewsletter();
  });

  function initLoader() {
    var loader = document.getElementById("lpLoader");
    if (!loader) return;
    window.addEventListener("load", function () {
      setTimeout(function () {
        loader.classList.add("lp-hidden");
      }, 250);
    });
  }

  function initAOS() {
    if (!window.AOS) return;
    var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    window.AOS.init({
      duration: 500,
      easing: "ease-out-cubic",
      once: true,
      offset: 40,
      // Scroll-reveal is skipped entirely on phone-sized viewports and for
      // reduced-motion users: content renders immediately instead of
      // depending on a scroll/IntersectionObserver trigger that can be
      // slow or unreliable on a weak mobile connection.
      disable: function () {
        return prefersReducedMotion || window.innerWidth < 768;
      },
    });
  }

  // Safety net: [data-aos] elements are opacity:0 until AOS adds
  // .aos-animate (or removes the attribute when disabled). If the AOS
  // script fails to load or run at all — slow/flaky network, blocked
  // request — those elements would stay invisible forever. Force them
  // visible after a short delay as a fallback so content is never
  // permanently hidden behind a JS dependency.
  function initAosSafetyNet() {
    setTimeout(function () {
      document.querySelectorAll("[data-aos]:not(.aos-animate)").forEach(function (el) {
        el.removeAttribute("data-aos");
      });
    }, 2500);
  }

  function initScrollProgress() {
    var bar = document.getElementById("lpScrollProgress");
    if (!bar) return;
    window.addEventListener(
      "scroll",
      function () {
        var scrollTop = window.scrollY;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        bar.style.width = pct + "%";
      },
      { passive: true }
    );
  }

  function initStickyNav() {
    var nav = document.getElementById("lpNavbar");
    if (!nav) return;
    function onScroll() {
      if (window.scrollY > 24) {
        nav.classList.add("lp-scrolled");
      } else {
        nav.classList.remove("lp-scrolled");
      }
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    var sections = document.querySelectorAll("main [id]");
    var navLinks = document.querySelectorAll(".lp-nav-links a, .lp-mobile-panel a");
    if (sections.length && navLinks.length && "IntersectionObserver" in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              var id = entry.target.getAttribute("id");
              navLinks.forEach(function (link) {
                link.classList.toggle("lp-active", link.getAttribute("href") === "#" + id);
              });
            }
          });
        },
        { rootMargin: "-45% 0px -50% 0px" }
      );
      sections.forEach(function (section) {
        observer.observe(section);
      });
    }
  }

  function initMobileMenu() {
    var toggle = document.getElementById("lpNavToggle");
    var panel = document.getElementById("lpMobilePanel");
    var close = document.getElementById("lpMobileClose");
    if (!toggle || !panel) return;

    function open() {
      panel.classList.add("lp-open");
      document.body.style.overflow = "hidden";
      toggle.setAttribute("aria-expanded", "true");
      if (close) close.focus();
      document.addEventListener("keydown", onKeydown);
    }
    function closePanel() {
      panel.classList.remove("lp-open");
      document.body.style.overflow = "";
      toggle.setAttribute("aria-expanded", "false");
      toggle.focus();
      document.removeEventListener("keydown", onKeydown);
    }
    function onKeydown(event) {
      if (event.key === "Escape") {
        closePanel();
        return;
      }
      // Basic focus containment while the panel is open.
      if (event.key === "Tab") {
        var focusable = panel.querySelectorAll('a[href], button:not([disabled])');
        if (!focusable.length) return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    }

    toggle.addEventListener("click", open);
    if (close) close.addEventListener("click", closePanel);
    panel.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", closePanel);
    });
  }

  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
      link.addEventListener("click", function (event) {
        var targetId = link.getAttribute("href");
        if (!targetId || targetId === "#") return;
        var target = document.querySelector(targetId);
        if (!target) return;
        event.preventDefault();
        var offset = 90;
        var top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: top, behavior: "smooth" });
      });
    });
  }

  function initBackToTop() {
    var btn = document.getElementById("lpBackToTop");
    if (!btn) return;
    window.addEventListener(
      "scroll",
      function () {
        btn.classList.toggle("lp-visible", window.scrollY > 480);
      },
      { passive: true }
    );
    btn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  function initTyped() {
    var el = document.getElementById("lpTyped");
    if (!el) return;
    var words = ["Admissions", "Attendance", "Results", "Finance", "Communication", "Timetables"];

    if (window.Typed) {
      new window.Typed("#lpTyped", {
        strings: words,
        typeSpeed: 55,
        backSpeed: 32,
        backDelay: 1400,
        loop: true,
      });
      return;
    }

    // Fallback rotator if the Typed.js CDN is unavailable.
    var i = 0;
    el.textContent = words[0];
    setInterval(function () {
      i = (i + 1) % words.length;
      el.textContent = words[i];
    }, 2200);
  }

  function initShowcaseTabs() {
    var tabs = document.querySelectorAll(".lp-showcase-tab");
    var panes = document.querySelectorAll(".lp-showcase-pane");
    if (!tabs.length) return;
    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        var target = tab.getAttribute("data-target");
        tabs.forEach(function (t) {
          t.classList.remove("lp-active");
        });
        panes.forEach(function (pane) {
          pane.classList.remove("lp-active");
        });
        tab.classList.add("lp-active");
        var pane = document.querySelector(target);
        if (pane) pane.classList.add("lp-active");
      });
    });
  }

  function initSwiper() {
    var container = document.querySelector(".lpTestimonialSwiper");
    if (!container || !window.Swiper) return;

    var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var swiper = new window.Swiper(".lpTestimonialSwiper", {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: prefersReducedMotion ? false : { delay: 4500, disableOnInteraction: false },
      navigation: {
        nextEl: "#lpSwiperNext",
        prevEl: "#lpSwiperPrev",
      },
      breakpoints: {
        768: { slidesPerView: 2 },
        1200: { slidesPerView: 3 },
      },
    });

    // WCAG 2.2.2 (Pause, Stop, Hide): give users an explicit control over
    // the auto-advancing carousel, in addition to pausing on hover/focus.
    var playPauseBtn = document.getElementById("lpSwiperPlayPause");
    if (playPauseBtn && swiper.autoplay) {
      var isPaused = prefersReducedMotion;

      function setPausedState(paused) {
        isPaused = paused;
        playPauseBtn.setAttribute("aria-pressed", paused ? "true" : "false");
        playPauseBtn.setAttribute("aria-label", paused ? "Play testimonial autoplay" : "Pause testimonial autoplay");
        playPauseBtn.innerHTML = paused
          ? '<i class="fa-solid fa-play" aria-hidden="true"></i>'
          : '<i class="fa-solid fa-pause" aria-hidden="true"></i>';
      }

      playPauseBtn.addEventListener("click", function () {
        if (isPaused) {
          swiper.autoplay.start();
          setPausedState(false);
        } else {
          swiper.autoplay.stop();
          setPausedState(true);
        }
      });

      container.addEventListener("mouseenter", function () {
        if (!isPaused) swiper.autoplay.stop();
      });
      container.addEventListener("mouseleave", function () {
        if (!isPaused) swiper.autoplay.start();
      });
      container.addEventListener("focusin", function () {
        if (!isPaused) swiper.autoplay.stop();
      });
      container.addEventListener("focusout", function () {
        if (!isPaused) swiper.autoplay.start();
      });

      if (prefersReducedMotion) setPausedState(true);
    }
  }

  function initCounters() {
    var counters = document.querySelectorAll(".lp-counter");
    if (!counters.length) return;

    function animate(el) {
      var target = parseFloat(el.getAttribute("data-count")) || 0;
      var decimals = target % 1 !== 0 ? 1 : 0;

      function manualFallback() {
        var stepTime = 16;
        var steps = Math.max(1, Math.round(2200 / stepTime));
        var increment = target / steps;
        var current = 0;
        var timer = setInterval(function () {
          current += increment;
          if (current >= target) {
            current = target;
            clearInterval(timer);
          }
          el.textContent = decimals ? current.toFixed(1) : Math.floor(current).toLocaleString();
        }, stepTime);
      }

      var CountUpCtor = window.CountUp || (window.countUp && window.countUp.CountUp);
      if (CountUpCtor) {
        var counter = new CountUpCtor(el, target, {
          duration: 2.2,
          decimalPlaces: decimals,
        });
        if (!counter.error) {
          counter.start();
          // Watchdog: if rAF-based CountUp hasn't moved the number shortly
          // after starting (e.g. throttled by a backgrounded tab), fall
          // back to the interval-based animation instead of staying at 0.
          setTimeout(function () {
            if (el.textContent.trim() === "0" && target > 0) {
              manualFallback();
            }
          }, 500);
          return;
        }
      }

      manualFallback();
    }

    if ("IntersectionObserver" in window) {
      var observer = new IntersectionObserver(
        function (entries, obs) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              animate(entry.target);
              obs.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.4 }
      );
      counters.forEach(function (counter) {
        observer.observe(counter);
      });
    } else {
      counters.forEach(animate);
    }
  }

  function initDemoForm() {
    var form = document.getElementById("lpDemoForm");
    if (!form) return;
    var success = document.getElementById("lpDemoSuccess");
    var serverError = document.getElementById("lpDemoFormError");

    var validators = {
      school_name: function (v) { return v.trim().length > 1; },
      contact_person: function (v) { return v.trim().length > 1; },
      phone: function (v) { return /^[+0-9()\-\s]{7,20}$/.test(v.trim()); },
      email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); },
      school_type: function (v) { return v.trim().length > 0; },
      student_population: function (v) { return v.trim().length > 0; },
    };

    function validateField(field) {
      var name = field.getAttribute("name");
      var rule = validators[name];
      if (!rule) return true;
      var valid = rule(field.value || "");
      var errorEl = form.querySelector('[data-error-for="' + name + '"]');
      field.classList.toggle("lp-invalid", !valid);
      field.setAttribute("aria-invalid", valid ? "false" : "true");
      if (errorEl) errorEl.classList.toggle("lp-show", !valid);
      return valid;
    }

    Array.prototype.forEach.call(form.elements, function (field) {
      if (!field.name || !validators[field.name]) return;
      field.addEventListener("blur", function () {
        validateField(field);
      });
      field.addEventListener("input", function () {
        if (field.classList.contains("lp-invalid")) validateField(field);
      });
    });

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var allValid = true;
      var firstInvalid = null;
      Array.prototype.forEach.call(form.elements, function (field) {
        if (field.name && validators[field.name] && !validateField(field)) {
          allValid = false;
          if (!firstInvalid) firstInvalid = field;
        }
      });
      if (!allValid) {
        if (firstInvalid) firstInvalid.focus();
        return;
      }

      if (serverError) {
        serverError.classList.remove("lp-show");
        serverError.textContent = "";
      }

      var submitBtn = form.querySelector('[type="submit"]');
      var originalLabel = submitBtn ? submitBtn.innerHTML : "";
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
      }

      fetch(form.getAttribute("action"), {
        method: "POST",
        body: new FormData(form),
        headers: { "X-Requested-With": "XMLHttpRequest" },
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok, data: data };
          });
        })
        .then(function (result) {
          if (result.ok && result.data.success) {
            form.style.display = "none";
            if (success) success.classList.add("lp-show");
            return;
          }
          throw new Error((result.data && result.data.message) || "Something went wrong. Please try again.");
        })
        .catch(function (error) {
          if (serverError) {
            serverError.textContent = error.message || "Something went wrong. Please try again.";
            serverError.classList.add("lp-show");
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalLabel;
          }
        });
    });
  }

  function initRipple() {
    document.querySelectorAll(".lp-btn").forEach(function (btn) {
      btn.addEventListener("click", function (event) {
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement("span");
        var size = Math.max(rect.width, rect.height);
        ripple.className = "lp-ripple";
        ripple.style.width = ripple.style.height = size + "px";
        ripple.style.left = event.clientX - rect.left - size / 2 + "px";
        ripple.style.top = event.clientY - rect.top - size / 2 + "px";
        btn.appendChild(ripple);
        setTimeout(function () {
          ripple.remove();
        }, 650);
      });
    });
  }

  function initNewsletter() {
    var form = document.getElementById("lpNewsletterForm");
    if (!form) return;
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      var input = form.querySelector("input");
      var btn = form.querySelector("button");
      if (!input || !input.value.trim()) return;
      btn.innerHTML = '<i class="fa-solid fa-check"></i>';
      input.value = "";
      setTimeout(function () {
        btn.innerHTML = "Subscribe";
      }, 2200);
    });
  }
})();
