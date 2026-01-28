/**
 * PayUNi Checkout Handler
 *
 * FluentCart checkout 會用 fragments replace 掉 payment container 的 HTML，
 * 所以這裡一律用「重繪 UI」的方式確保畫面穩定、漂亮、可互動。
 *
 * UI/UX: Exaggerated Minimalism（乾淨留白 + 清楚的選取狀態）
 */
(function () {
  if (window.__buygoFcPayuniCheckoutUiLoaded) {
    return;
  }

  window.__buygoFcPayuniCheckoutUiLoaded = true;

  const ACCENT = '#136196';

  function createEl(tag, cls, text) {
    const el = document.createElement(tag);
    if (cls) {
      el.className = cls;
    }
    if (typeof text === 'string') {
      el.textContent = text;
    }
    return el;
  }

  /** Phase 3: 付款方式小圖示（currentColor 適配亮/暗底） */
  function createMethodIcon(type) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', 'buygo-payuni-method-icon');
    svg.setAttribute('width', '24');
    svg.setAttribute('height', '24');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.5');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');

    if (type === 'credit') {
      svg.innerHTML =
        '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>';
    } else if (type === 'atm') {
      svg.innerHTML =
        '<path d="M2 10h9l-2.5-2.5m0 5l2.5-2.5"/>' +
        '<path d="M22 14H13l2.5 2.5m0-5l-2.5 2.5"/>';
    } else if (type === 'cvs') {
      svg.innerHTML =
        '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>' +
        '<path d="M9 22V12h6v10"/>';
    } else {
      svg.innerHTML = '<rect x="2" y="5" width="20" height="14" rx="2"/>';
    }

    return svg;
  }

  function findCheckoutForm() {
    const methodInput = document.querySelector("input[name='_fct_pay_method']");
    if (methodInput) {
      const form = methodInput.closest('form');
      if (form) {
        return form;
      }
    }

    return document.querySelector('form');
  }

  function storageGet(key) {
    try {
      return window.sessionStorage.getItem(key) || '';
    } catch (e) {
      return '';
    }
  }

  function storageSet(key, val) {
    try {
      window.sessionStorage.setItem(key, val || '');
    } catch (e) {
      // ignore
    }
  }

  function ensureHidden(name, value) {
    const form = findCheckoutForm();
    if (!form) {
      return;
    }

    let hidden = form.querySelector(`input[name='${name}'][type='hidden']`);
    if (!hidden) {
      hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = name;
      form.appendChild(hidden);
    }

    hidden.value = value || '';
  }

  function ensureStyles(payuniData) {
    if (document.getElementById('buygo-payuni-ui-style-link')) {
      return;
    }

    const href =
      payuniData.css_url ||
      (function () {
        return '';
      })();

    if (!href) {
      return;
    }

    const link = document.createElement('link');
    link.id = 'buygo-payuni-ui-style-link';
    link.rel = 'stylesheet';
    link.href = href;
    document.head.appendChild(link);
  }

  function findContainer(route) {
    return (
      document.querySelector(`.fluent-cart-checkout_embed_payment_container_${route}`) ||
      document.querySelector(
        `.fluent-cart-checkout_embed_payment_container_${route.replace(/_/g, '-')}`
      )
    );
  }

  function bindGatewayUi(route, options) {
    const opts = options || {};
    const allowPayTypeSelect = opts.allowPayTypeSelect !== false;
    const allowedPayTypes = Array.isArray(opts.allowedPayTypes) ? opts.allowedPayTypes : null;
    const displayPayTypes = Array.isArray(opts.displayPayTypes) ? opts.displayPayTypes : null;
    const data =
      (route === 'payuni_subscription'
        ? window.buygo_fc_payuni_subscription_data
        : window.buygo_fc_payuni_data) ||
      window.buygo_fc_payuni_data ||
      {};

    const defaultDescription =
      route === 'payuni_subscription'
        ? '使用 PayUNi 信用卡定期定額付款（初次需 3D 驗證）。'
        : '使用 PayUNi（統一金流）付款。信用卡可站內刷卡並進行 3D 驗證，ATM/超商將直接取號顯示於收據頁。';

    const description = data.description || defaultDescription;
    const storagePrefix = route === 'payuni' ? 'payuni' : 'payuni_subscription';

    function clearHiddenCardFields() {
      ensureHidden('payuni_card_number', '');
      ensureHidden('payuni_card_expiry', '');
      ensureHidden('payuni_card_cvc', '');
    }

    function clearHiddenAtmFields() {
      ensureHidden('payuni_bank_type', '');
    }

    function getSelectedPayType() {
      if (!allowPayTypeSelect) {
        return 'credit';
      }

      const stored = storageGet(`buygo_fc_${storagePrefix}_pay_type`);
      const v = stored || 'credit';
      if (allowedPayTypes && !allowedPayTypes.includes(v)) {
        return allowedPayTypes[0] || 'credit';
      }
      return v;
    }

    function getSelectedBankType() {
      const stored = storageGet(`buygo_fc_${storagePrefix}_bank_type`);
      return stored || '004';
    }

    function setSelectedBankType(val) {
      const v = val || '004';
      storageSet(`buygo_fc_${storagePrefix}_bank_type`, v);
      ensureHidden('payuni_bank_type', v);
    }

    function setPayType(val) {
      const v = val || 'credit';

      if (allowedPayTypes && !allowedPayTypes.includes(v)) {
        return;
      }

      if (allowPayTypeSelect) {
        storageSet(`buygo_fc_${storagePrefix}_pay_type`, v);
        ensureHidden('payuni_payment_type', v);
      }

      if (v !== 'credit') {
        clearHiddenCardFields();
      }

      if (v !== 'atm') {
        clearHiddenAtmFields();
      }
    }

    function render(container) {
      if (!container) {
        return;
      }

      ensureStyles(data);

      const payType = getSelectedPayType();
      setPayType(payType);

      container.innerHTML = '';

      const root = createEl('div', 'buygo-payuni');
      root.style.setProperty('--buygo-payuni-accent', data.accent || ACCENT);
      const card = createEl('div', 'card');

      const methodSection = createEl('div', 'section');
      methodSection.appendChild(createEl('div', 'section-title', '付款方式'));
      const methods = createEl('div', 'methods');

      function methodCard(value, title, sub, config) {
        const cfg = config || {};
        const selected = (cfg.selectedValue || payType) === value;
        const label = createEl('label', 'method' + (selected ? ' selected' : ''));
        label.setAttribute('data-payuni-method', value);

        const left = createEl('div', 'left');
        const iconWrap = createEl('div', 'method-icon-wrap');
        iconWrap.appendChild(createMethodIcon(value));

        const input = document.createElement('input');
        input.type = 'radio';
        input.name = cfg.inputName || 'payuni_payment_type';
        input.value = value;
        input.className = 'radio payuni-radio-hidden';
        input.checked = selected;
        if (cfg.disabled) {
          input.disabled = true;
        }

        const textWrap = createEl('div');
        textWrap.appendChild(createEl('div', 'label', title));
        if (sub) {
          textWrap.appendChild(createEl('div', 'desc small muted', sub));
        }

        left.appendChild(iconWrap);
        left.appendChild(input);
        left.appendChild(textWrap);

        label.appendChild(left);

        return label;
      }

      const payTypeCards = [
        { value: 'credit', title: '信用卡', sub: '' },
        { value: 'atm', title: 'ATM 轉帳', sub: '' },
        { value: 'cvs', title: '超商繳費', sub: '' },
      ];

      payTypeCards.forEach(function (c) {
        if (displayPayTypes && !displayPayTypes.includes(c.value)) {
          return;
        }

        methods.appendChild(
          methodCard(c.value, c.title, c.sub, {
            disabled: allowedPayTypes ? !allowedPayTypes.includes(c.value) : false,
          })
        );
      });

      methodSection.appendChild(methods);

      card.appendChild(methodSection);

      if (payType === 'credit') {
        const section = createEl('div', 'section');
        section.appendChild(createEl('div', 'section-title', '信用卡資料'));
        section.style.marginLeft = '0';
        section.style.paddingLeft = '0';

        var secureRow = createEl('div', 'payment-secure');
        secureRow.style.marginLeft = '0';
        secureRow.style.paddingLeft = '0';
        var lockSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        lockSvg.setAttribute('viewBox', '0 0 24 24');
        lockSvg.setAttribute('fill', 'none');
        lockSvg.setAttribute('stroke', 'currentColor');
        lockSvg.setAttribute('stroke-width', '2');
        lockSvg.setAttribute('stroke-linecap', 'round');
        lockSvg.setAttribute('stroke-linejoin', 'round');
        lockSvg.innerHTML =
          '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>';
        secureRow.appendChild(lockSvg);
        secureRow.appendChild(
          document.createTextNode('安全加密付款，由統一金流處理，卡號不會儲存於本站。')
        );
        section.appendChild(secureRow);

        var inputReset = { padding: '10px 12px', margin: '0', marginLeft: '0', paddingLeft: '12px', textIndent: '0', boxSizing: 'border-box' };
        var fieldReset = { margin: '0', padding: '0' };
        var labelReset = { margin: '0', padding: '0' };

        const grid = createEl('div', 'grid');
        grid.style.gap = '6px';
        grid.style.marginLeft = '0';
        grid.style.paddingLeft = '0';

        const f1 = createEl('div', 'field');
        Object.assign(f1.style, fieldReset);
        const l1 = createEl('label', null, '卡號');
        l1.setAttribute('for', `buygo_${route}_card_number`);
        Object.assign(l1.style, labelReset);
        const i1 = document.createElement('input');
        i1.type = 'tel';
        i1.id = `buygo_${route}_card_number`;
        i1.className = 'payuni-input-card-number';
        i1.autocomplete = 'cc-number';
        i1.inputMode = 'numeric';
        i1.placeholder = '4242 4242 4242 4242';
        Object.assign(i1.style, inputReset);
        f1.appendChild(l1);
        f1.appendChild(i1);
        grid.appendChild(f1);

        const row = createEl('div', 'grid-2');
        row.style.gap = '8px 12px';
        row.style.marginLeft = '0';
        row.style.paddingLeft = '0';

        const f2 = createEl('div', 'field');
        Object.assign(f2.style, fieldReset);
        const l2 = createEl('label', null, '有效期限（MM/YY）');
        l2.setAttribute('for', `buygo_${route}_card_expiry`);
        Object.assign(l2.style, labelReset);
        const i2 = document.createElement('input');
        i2.type = 'tel';
        i2.id = `buygo_${route}_card_expiry`;
        i2.autocomplete = 'cc-exp';
        i2.inputMode = 'numeric';
        i2.placeholder = '12/30';
        Object.assign(i2.style, inputReset);
        f2.appendChild(l2);
        f2.appendChild(i2);

        const f3 = createEl('div', 'field');
        Object.assign(f3.style, fieldReset);
        const l3 = createEl('label', null, '安全碼（CVC）');
        l3.setAttribute('for', `buygo_${route}_card_cvc`);
        Object.assign(l3.style, labelReset);
        const i3 = document.createElement('input');
        i3.type = 'tel';
        i3.id = `buygo_${route}_card_cvc`;
        i3.autocomplete = 'cc-csc';
        i3.inputMode = 'numeric';
        i3.placeholder = '123';
        Object.assign(i3.style, inputReset);
        f3.appendChild(l3);
        f3.appendChild(i3);

        row.appendChild(f2);
        row.appendChild(f3);
        grid.appendChild(row);

        section.appendChild(grid);

        card.appendChild(section);

        function syncCardToHidden() {
          const number = (i1.value || '').replace(/\s+/g, '');
          const expiry = (i2.value || '').replace(/\s+/g, '').replace(/[/-]/g, '');
          const cvc = (i3.value || '').replace(/\s+/g, '');

          ensureHidden('payuni_card_number', number);
          ensureHidden('payuni_card_expiry', expiry);
          ensureHidden('payuni_card_cvc', cvc);
        }

        i1.addEventListener('input', syncCardToHidden);
        i2.addEventListener('input', syncCardToHidden);
        i3.addEventListener('input', syncCardToHidden);
        syncCardToHidden();
      }

      if (allowPayTypeSelect && payType === 'atm') {
        var fieldResetAtm = { margin: '0', padding: '0' };
        var labelResetAtm = { margin: '0', padding: '0' };
        var selectResetAtm = { padding: '10px 12px', margin: '0', marginLeft: '0', paddingLeft: '12px', boxSizing: 'border-box' };

        const section = createEl('div', 'section');
        section.style.marginLeft = '0';
        section.style.paddingLeft = '0';
        const atmHint = createEl('p', 'method-only-hint', 'ATM 轉帳：送出後將於收據頁顯示轉帳帳號與繳費期限。');
        section.appendChild(atmHint);

        const grid = createEl('div', 'grid');
        grid.style.gap = '6px';
        grid.style.marginLeft = '0';
        grid.style.paddingLeft = '0';

        const f1 = createEl('div', 'field');
        Object.assign(f1.style, fieldResetAtm);
        const l1 = createEl('label', null, '轉帳銀行');
        l1.setAttribute('for', `buygo_${route}_bank_type`);
        Object.assign(l1.style, labelResetAtm);
        const s1 = document.createElement('select');
        s1.id = `buygo_${route}_bank_type`;
        Object.assign(s1.style, selectResetAtm);

        const opts = [
          { value: '004', label: '台灣銀行（004）' },
          { value: '822', label: '中國信託（822）' },
          { value: '013', label: '國泰世華（013）' },
        ];

        const current = getSelectedBankType();
        opts.forEach(function (o) {
          const opt = document.createElement('option');
          opt.value = o.value;
          opt.textContent = o.label;
          if (o.value === current) {
            opt.selected = true;
          }
          s1.appendChild(opt);
        });

        f1.appendChild(l1);
        f1.appendChild(s1);
        grid.appendChild(f1);

        section.appendChild(grid);

        card.appendChild(section);

        setSelectedBankType(current);
        s1.addEventListener('change', function () {
          setSelectedBankType(s1.value);
        });
      }

      if (allowPayTypeSelect && payType === 'cvs') {
        const section = createEl('div', 'section');
        const cvsHint = createEl('p', 'method-only-hint', '超商繳費：送出後將於收據頁顯示繳費代碼與期限。');
        section.appendChild(cvsHint);
        card.appendChild(section);
      }

      var footerHintText =
        route === 'payuni_subscription'
          ? '使用 PayUNi 信用卡定期定額付款，初次需 3D 驗證，後續將由系統自動續扣。'
          : '使用 PayUNi（統一金流）付款。信用卡可站內刷卡並進行 3D 驗證，ATM/超商將直接取號顯示於收據頁。';
      var footerHint = createEl('p', 'page-footer-hint', footerHintText);
      card.appendChild(footerHint);

      root.appendChild(card);
      container.appendChild(root);
    }

    function markReady() {
      try {
        window[`is_${route}_ready`] = true;
      } catch (e) {
        // ignore
      }
    }

    function enableCheckoutButton(event) {
      const submitButton = window.fluentcart_checkout_vars?.submit_button;
      const txt = submitButton?.text || '送出訂單';

      if (event?.detail?.paymentLoader?.enableCheckoutButton) {
        event.detail.paymentLoader.enableCheckoutButton(txt);
        return;
      }

      if (
        window.fluent_cart_checkout_ui_service &&
        window.fluent_cart_checkout_ui_service.enableCheckoutButton
      ) {
        window.fluent_cart_checkout_ui_service.enableCheckoutButton();
        if (window.fluent_cart_checkout_ui_service.setCheckoutButtonText) {
          window.fluent_cart_checkout_ui_service.setCheckoutButtonText(txt);
        }
      }
    }

    function run(event) {
      const container = findContainer(route);
      if (!container) {
        return;
      }

      render(container);
      markReady();
      enableCheckoutButton(event);
    }

    window.addEventListener(`fluent_cart_load_payments_${route}`, run);
    window.addEventListener(`fluent_cart_load_payments_${route.replace(/_/g, '-')}`, run);

    window.__buygoFcPayuniUiRunners = window.__buygoFcPayuniUiRunners || {};
    window.__buygoFcPayuniUiRunners[route] = function (event) {
      run(event);
    };

    return {
      route,
      run,
      setPayType,
    };
  }

  const payuniUi = bindGatewayUi('payuni', { allowPayTypeSelect: true });
  const subUi = bindGatewayUi('payuni_subscription', {
    allowPayTypeSelect: true,
    allowedPayTypes: ['credit'],
    displayPayTypes: ['credit'],
  });

  function getSelectedMethod() {
    const el = document.querySelector("input[name='_fct_pay_method']:checked");
    return el ? el.value : '';
  }

  function runIfSelected(event) {
    const m = getSelectedMethod();
    if (m === 'payuni' && payuniUi?.run) {
      payuniUi.run(event);
      return;
    }
    if (m === 'payuni_subscription' && subUi?.run) {
      subUi.run(event);
      return;
    }
  }

  if (!window.__buygoFcPayuniUiBound) {
    window.__buygoFcPayuniUiBound = true;

    window.addEventListener('fluent_cart_after_checkout_js_loaded', runIfSelected);
    window.addEventListener('fluentCartFragmentsReplaced', function () {
      setTimeout(function () {
        runIfSelected();
      }, 0);
    });

    document.addEventListener('change', function (e) {
      const t = e && e.target;
      if (!t) {
        return;
      }

      if (t.name === '_fct_pay_method' && t.checked) {
        runIfSelected();
        return;
      }

      if (t.name === 'payuni_payment_type') {
        if (payuniUi?.setPayType) {
          payuniUi.setPayType(t.value);
        }
        if (subUi?.setPayType) {
          subUi.setPayType(t.value);
        }
        runIfSelected();
        return;
      }
    });
  }

  setTimeout(function () {
    runIfSelected();
  }, 0);
})();

