/**
 * 前台會員訂閱頁「更新付款」：若為 PayUNi 訂閱，modal 打開時載入換卡表單並由本外掛 API 處理送出。
 */
(function () {
  'use strict';

  var MODAL_TITLE_TEXTS = ['更新付款', 'Update Payment'];
  var INJECTED_CLASS = 'buygo-payuni-card-form-injected';

  function getConfig() {
    return window.buygo_fc_payuni_account || {};
  }

  function getSubscriptionUuidFromPath() {
    var path = (window.location.pathname || '').replace(/\/+$/, '');
    var parts = path.split('/');
    var idx = parts.indexOf('subscription');
    if (idx >= 0 && parts[idx + 1]) {
      return parts[idx + 1];
    }
    if (parts.length >= 2 && /^[a-zA-Z0-9\-]{20,}$/.test(parts[parts.length - 1])) {
      return parts[parts.length - 1];
    }
    var hash = (window.location.hash || '').replace(/^#\/?/, '');
    var hashParts = hash.split('/');
    var hashIdx = hashParts.indexOf('subscriptions');
    if (hashIdx >= 0 && hashParts[hashIdx + 1] && /^[a-zA-Z0-9\-]{20,}$/.test(hashParts[hashIdx + 1])) {
      return hashParts[hashIdx + 1];
    }
    return null;
  }

  function fetchCardForm(restUrl, uuid, nonce) {
    var base = (restUrl || '').replace(/\/+$/, '');
    var url = base + '/subscriptions/' + encodeURIComponent(uuid) + '/card-form';
    return fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-WP-Nonce': nonce || '',
      },
    }).then(function (res) { return res.json(); });
  }

  function postCardUpdate(restUrl, uuid, data, nonce) {
    var base = (restUrl || '').replace(/\/+$/, '');
    var url = base + '/subscriptions/' + encodeURIComponent(uuid) + '/card-update';
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-WP-Nonce': nonce || '',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    }).then(function (res) { return res.json(); });
  }

  function isVisible(el) {
    if (!el) return false;
    var rect = el.getBoundingClientRect();
    return rect.width > 0 && rect.height > 0;
  }

  /**
   * 找到「更新付款」modal 內要塞表單的容器：只認「可見」的 dialog，優先 .fct-update-payment-method-form，否則 .el-dialog__body。
   */
  function findUpdatePaymentModalFormContainer() {
    var dialogs = document.querySelectorAll('.el-dialog, [role="dialog"]');
    for (var i = 0; i < dialogs.length; i++) {
      var d = dialogs[i];
      if (!isVisible(d)) continue;
      var titleEl = d.querySelector('.el-dialog__title, [class*="dialog"] [class*="title"]');
      var title = titleEl ? (titleEl.textContent || '').trim() : '';
      if (MODAL_TITLE_TEXTS.some(function (t) { return title.indexOf(t) !== -1; })) {
        var formContainer = d.querySelector('.fct-update-payment-method-form');
        if (formContainer) return formContainer;
        var body = d.querySelector('.el-dialog__body, [class*="dialog__body"]');
        if (body) return body;
      }
    }
    return null;
  }

  /**
   * Modal 打開時呼叫 card-form API 並注入表單。只認可見 modal，並延遲 150ms 讓 Vue 先渲染完再注入，減少需重複整理的問題。
   */
  function injectFormWhenModalOpen(subscriptionUuid, config) {
    var restUrl = config.restUrl;
    var nonce = config.nonce;
    if (!restUrl || !subscriptionUuid) return;

    var scheduled = null;

    function tryInject() {
      var container = findUpdatePaymentModalFormContainer();
      if (!container || container.classList.contains(INJECTED_CLASS)) return;
      scheduled = null;
      container.classList.add(INJECTED_CLASS);
      container.innerHTML = '<p class="buygo-payuni-loading">載入中…</p>';
      fetchCardForm(restUrl, subscriptionUuid, nonce).then(function (res) {
        if (res && res.code === 'rest_no_route') {
          container.innerHTML = '<p class="buygo-payuni-error">無法載入換卡表單。</p>';
          return;
        }
        var html = (res && res.html) ? res.html : '';
        if (html) {
          container.innerHTML = html;
          bindFormSubmit(container, subscriptionUuid, config);
        } else {
          container.innerHTML = '<p class="buygo-payuni-error">無法載入換卡表單。</p>';
        }
      }).catch(function () {
        container.innerHTML = '<p class="buygo-payuni-error">無法載入換卡表單，請稍後再試。</p>';
      });
    }

    function scheduleInject() {
      var container = findUpdatePaymentModalFormContainer();
      if (!container || container.classList.contains(INJECTED_CLASS) || scheduled) return;
      scheduled = window.setTimeout(function () {
        scheduled = null;
        tryInject();
      }, 150);
    }

    var observer = new MutationObserver(function () {
      var container = findUpdatePaymentModalFormContainer();
      if (container && !container.classList.contains(INJECTED_CLASS)) {
        scheduleInject();
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    var check = setInterval(function () {
      var container = findUpdatePaymentModalFormContainer();
      if (container && !container.classList.contains(INJECTED_CLASS) && container.children.length <= 1) {
        scheduleInject();
        clearInterval(check);
      }
    }, 300);
    setTimeout(function () { clearInterval(check); }, 15000);
  }

  function bindFormSubmit(container, subscriptionUuid, config) {
    var wrap = container.querySelector('.buygo-payuni') || container;
    if (!wrap) return;

    var restUrl = config.restUrl;
    var nonce = config.nonce;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'el-button el-button--primary';
    btn.textContent = '更新付款方式';
    btn.style.marginTop = '12px';
    wrap.appendChild(btn);

    function doSubmit() {
      var num = (wrap.querySelector('[name="payuni_card_number"]') || {}).value || '';
      var exp = (wrap.querySelector('[name="payuni_card_expiry"]') || {}).value || '';
      var cvc = (wrap.querySelector('[name="payuni_card_cvc"]') || {}).value || '';
      if (!num || !exp || !cvc) {
        alert('請填寫卡號、有效期限與安全碼。');
        return;
      }

      btn.disabled = true;
      btn.setAttribute('aria-busy', 'true');

      postCardUpdate(restUrl, subscriptionUuid, {
        payuni_card_number: num,
        payuni_card_expiry: exp,
        payuni_card_cvc: cvc,
      }, nonce).then(function (data) {
        if (data.redirect_url) {
          window.location.href = data.redirect_url;
          return;
        }
        if (data.message) {
          alert(data.message);
        }
        var container = findUpdatePaymentModalFormContainer();
        if (container) {
          container.classList.remove(INJECTED_CLASS);
          container.innerHTML = '';
        }
        if (window.fluentcart && typeof window.fluentcart.$message === 'function') {
          window.fluentcart.$message({ type: 'success', message: data.message || '已更新' });
        }
        if (typeof window.location.reload === 'function') {
          window.location.reload();
        }
      }).catch(function () {
        alert('更新失敗，請稍後再試。');
      }).finally(function () {
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
      });
    }

    btn.addEventListener('click', doSubmit);

    var form = wrap.querySelector('form');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        doSubmit();
      });
    }
  }

  function run() {
    var config = getConfig();
    var uuid = getSubscriptionUuidFromPath();
    if (!uuid || !config.restUrl) return;

    injectFormWhenModalOpen(uuid, config);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }
})();
