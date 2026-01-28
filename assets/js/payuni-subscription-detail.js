/**
 * PayUNi 訂閱詳情頁操作 UI
 * 僅在 FluentCart 後台「訂閱詳情」且該訂閱為 payuni_subscription 時，注入「同步訂閱狀態」「查看 PayUNi 交易明細」按鈕。
 */
(function () {
  'use strict';

  var CONTAINER_ID = 'payuni-subscription-actions';
  // 訂閱詳情實際 hash：#/subscriptions/3/view
  var HASH_PATTERN = /#\/subscriptions\/(\d+)(?:\/view)?/;

  function getConfig() {
    return window.buygo_fc_payuni_subscription_detail || {};
  }

  function getSubscriptionIdFromHash() {
    var hash = window.location.hash || '';
    var m = hash.match(HASH_PATTERN);
    return m ? parseInt(m[1], 10) : null;
  }

  function removeInjectedUI() {
    var el = document.getElementById(CONTAINER_ID);
    if (el && el.parentNode) {
      el.parentNode.removeChild(el);
    }
  }

  function injectUI(actions, subscription) {
    removeInjectedUI();

    // 使用與 FluentCart 後台一致的 Element Plus 結構，讓樣式由既有 CSS 套用
    var container = document.createElement('div');
    container.id = CONTAINER_ID;
    container.setAttribute('class', 'el-card payuni-subscription-actions');
    container.style.marginTop = '16px';

    var header = document.createElement('div');
    header.setAttribute('class', 'el-card__header');
    header.textContent = (subscription.payment_method_display_name || getConfig().displayName || 'PayUNi（統一金流）');
    container.appendChild(header);

    var body = document.createElement('div');
    body.setAttribute('class', 'el-card__body');

    var wrap = document.createElement('div');
    wrap.setAttribute('class', 'payuni-subscription-actions__buttons');
    wrap.style.display = 'flex';
    wrap.style.flexWrap = 'wrap';
    wrap.style.gap = '8px';

    actions.forEach(function (item) {
      if (item.type === 'link' && item.url) {
        var a = document.createElement('a');
        a.href = item.url;
        a.target = '_blank';
        a.rel = 'noopener';
        a.setAttribute('class', 'el-button el-button--default');
        a.textContent = item.label || '查看 PayUNi 交易明細';
        if (item.tooltip) {
          a.setAttribute('title', item.tooltip);
        }
        wrap.appendChild(a);
      } else if (item.action === 'fetch') {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('class', 'el-button el-button--default');
        btn.textContent = item.label || '同步訂閱狀態';
        if (item.tooltip) {
          btn.setAttribute('title', item.tooltip);
        }
        btn.addEventListener('click', function () {
          fetchSubscription(subscription, btn);
        });
        wrap.appendChild(btn);
      }
    });

    // 取消訂閱：僅在非已取消狀態時顯示
    var status = (subscription.status || '').toLowerCase();
    if (status !== 'canceled' && status !== 'cancelled' && status !== 'completed') {
      var cancelBtn = document.createElement('button');
      cancelBtn.type = 'button';
      cancelBtn.setAttribute('class', 'el-button el-button--danger');
      cancelBtn.textContent = '取消訂閱';
      cancelBtn.setAttribute('title', '將此訂閱標記為已取消，不再續扣');
      cancelBtn.addEventListener('click', function () {
        cancelSubscription(subscription, cancelBtn);
      });
      wrap.appendChild(cancelBtn);
    }

    body.appendChild(wrap);
    container.appendChild(body);

    // 插入到訂閱詳情頁「內容區塊的底部」，不擋住上方訂閱資訊，用戶看完內容再操作
    var main = document.querySelector('#app .el-main, #wpbody-content .el-main, .el-main');
    var app = document.getElementById('app');
    var wpbody = document.getElementById('wpbody-content');
    if (main) {
      main.appendChild(container);
    } else if (app) {
      app.appendChild(container);
    } else if (wpbody) {
      wpbody.appendChild(container);
    }
  }

  function fetchSubscription(subscription, buttonEl) {
    var config = getConfig();
    var restUrl = config.restUrl;
    var nonce = config.nonce;
    if (!restUrl || !nonce) {
      alert('設定遺失，無法同步');
      return;
    }

    var orderId = subscription.parent_order_id;
    var subscriptionId = subscription.id;
    if (!orderId || !subscriptionId) {
      alert('訂閱資料不完整');
      return;
    }

    var url = restUrl + 'orders/' + orderId + '/subscriptions/' + subscriptionId + '/fetch';
    var originalText = buttonEl.textContent;
    buttonEl.disabled = true;
    buttonEl.textContent = '同步中…';

    fetch(url, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
      },
      credentials: 'same-origin',
    })
      .then(function (res) {
        var ct = res.headers.get('content-type');
        if (ct && ct.indexOf('application/json') !== -1) {
          return res.json().then(function (data) {
            return { ok: res.ok, body: data };
          });
        }
        return { ok: false, body: { message: res.status === 403 ? '無權限' : '伺服器回傳非 JSON' } };
      })
      .then(function (result) {
        // FluentCart sendSuccess 回傳 { message, subscription }，沒有包在 data 裡
        var body = result.body || {};
        var msg = body.message;
        var subscription = body.subscription;

        if (result.ok && subscription) {
          var successMsg = msg || '已從本站重新載入訂閱狀態與下次扣款日';
          if (typeof window.Notify !== 'undefined' && window.Notify.success) {
            window.Notify.success(successMsg);
          } else {
            alert(successMsg);
          }
          if (typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new CustomEvent('payuni-subscription-fetched', { detail: body }));
          }
          // 重新載入頁面，讓 FluentCart 訂閱詳情顯示更新後的狀態與下次扣款日
          setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          // Phase 1.2：同步失敗時僅顯示錯誤，不重整頁面
          var errMsg = msg || '同步失敗';
          if (typeof window.Notify !== 'undefined' && window.Notify.error) {
            window.Notify.error(errMsg);
          } else {
            alert(errMsg);
          }
        }
      })
      .catch(function (err) {
        var msg = (err && err.message) ? err.message : '網路錯誤或無法連線，請稍後再試';
        if (typeof window.Notify !== 'undefined' && window.Notify.error) {
          window.Notify.error(msg);
        } else {
          alert(msg);
        }
      })
      .finally(function () {
        buttonEl.disabled = false;
        buttonEl.textContent = originalText;
      });
  }

  function cancelSubscription(subscription, buttonEl) {
    var config = getConfig();
    var restUrl = config.restUrl;
    var nonce = config.nonce;
    if (!restUrl || !nonce) {
      alert('設定遺失，無法取消訂閱');
      return;
    }

    var orderId = subscription.parent_order_id;
    var subscriptionId = subscription.id;
    if (!orderId || !subscriptionId) {
      alert('訂閱資料不完整');
      return;
    }

    if (!window.confirm('確定要取消此訂閱？取消後將不再自動扣款。')) {
      return;
    }

    var url = restUrl + 'orders/' + orderId + '/subscriptions/' + subscriptionId + '/cancel';
    var originalText = buttonEl.textContent;
    buttonEl.disabled = true;
    buttonEl.textContent = '取消中…';

    fetch(url, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ cancel_reason: 'customer_request' }),
    })
      .then(function (res) {
        var ct = res.headers.get('content-type');
        if (ct && ct.indexOf('application/json') !== -1) {
          return res.json().then(function (data) {
            return { ok: res.ok, body: data };
          });
        }
        return { ok: false, body: { message: res.status === 403 ? '無權限' : '伺服器回傳非 JSON' } };
      })
      .then(function (result) {
        var body = result.body || {};
        var msg = body.message;

        if (result.ok) {
          var successMsg = msg || '訂閱已成功取消';
          if (typeof window.Notify !== 'undefined' && window.Notify.success) {
            window.Notify.success(successMsg);
          } else {
            alert(successMsg);
          }
          setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          // Phase 1.2：取消失敗時僅顯示後端錯誤訊息，不重整頁面
          var errMsg = msg || '取消失敗';
          if (typeof window.Notify !== 'undefined' && window.Notify.error) {
            window.Notify.error(errMsg);
          } else {
            alert(errMsg);
          }
          buttonEl.disabled = false;
          buttonEl.textContent = originalText;
        }
      })
      .catch(function (err) {
        var msg = (err && err.message) ? err.message : '網路錯誤或無法連線，請稍後再試';
        if (typeof window.Notify !== 'undefined' && window.Notify.error) {
          window.Notify.error(msg);
        } else {
          alert(msg);
        }
        buttonEl.disabled = false;
        buttonEl.textContent = originalText;
      });
  }

  function loadAndMaybeInject(subscriptionOrderId) {
    var config = getConfig();
    var restUrl = config.restUrl;
    var nonce = config.nonce;
    if (!restUrl || !nonce) {
      return;
    }

    var url = restUrl + 'subscriptions/' + subscriptionOrderId;

    fetch(url, {
      headers: {
        'X-WP-Nonce': nonce,
      },
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        var subscription = data && data.subscription;
        if (!subscription || (subscription.current_payment_method !== 'payuni_subscription')) {
          removeInjectedUI();
          return;
        }
        var actions = subscription.payuni_gateway_actions;
        if (Array.isArray(actions) && actions.length) {
          // 延遲注入，等 Vue 訂閱詳情版面渲染完再插入，避免區塊被蓋住或找不到插入點
          setTimeout(function () {
            injectUI(actions, subscription);
          }, 400);
        } else {
          removeInjectedUI();
        }
      })
      .catch(function () {
        removeInjectedUI();
      });
  }

  function run() {
    var id = getSubscriptionIdFromHash();
    if (id) {
      loadAndMaybeInject(id);
    } else {
      removeInjectedUI();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

  window.addEventListener('hashchange', run);

  // FluentCart 可能用 Vue Router 改 hash 而不觸發 hashchange，短輪詢作為備援
  var lastHash = window.location.hash;
  setInterval(function () {
    if (window.location.hash !== lastHash) {
      lastHash = window.location.hash;
      run();
    }
  }, 500);
})();
