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
        showCancelReasonModal(subscription, cancelBtn);
      });
      wrap.appendChild(cancelBtn);
    }

    // 重新啟用訂閱：僅在已取消狀態時顯示（管理員可為客戶重新啟用）
    // 依 status 或 canceled_at 判斷，避免 API 回傳格式（如 i18n、不同 key）導致按鈕不顯示
    var isCompleted = status === 'completed';
    var isCanceled = !isCompleted && (
      status === 'canceled' || status === 'cancelled' || (status && status.indexOf('cancel') !== -1) ||
      (subscription.canceled_at && String(subscription.canceled_at).trim() !== '')
    );
    if (isCanceled) {
      var reactivateBtn = document.createElement('button');
      reactivateBtn.type = 'button';
      reactivateBtn.setAttribute('class', 'el-button el-button--primary');
      reactivateBtn.textContent = '重新啟用訂閱';
      reactivateBtn.setAttribute('title', '將此訂閱重新啟用，恢復自動扣款');
      reactivateBtn.addEventListener('click', function () {
        reactivateSubscription(subscription, reactivateBtn);
      });
      wrap.appendChild(reactivateBtn);

      var cancelReason = subscription.config && subscription.config.cancellation_reason;
      if (cancelReason) {
        var reasonLabel = (CANCEL_REASON_OPTIONS.filter(function (o) { return o.value === cancelReason; })[0] || {}).label || cancelReason;
        var reasonRow = document.createElement('div');
        reasonRow.style.cssText = 'margin-top:12px;padding-top:12px;border-top:1px solid #ebeef5;font-size:13px;color:#606266;';
        reasonRow.textContent = '取消原因：' + reasonLabel;
        wrap.appendChild(reasonRow);
      }
    }

    body.appendChild(wrap);

    // Phase 4：下次扣款日檢視與編輯（僅在未取消時顯示）
    if (status !== 'canceled' && status !== 'cancelled' && status !== 'completed') {
      var nextBillingRow = document.createElement('div');
      nextBillingRow.setAttribute('class', 'payuni-next-billing-row');
      nextBillingRow.style.marginTop = '16px';
      nextBillingRow.style.paddingTop = '16px';
      nextBillingRow.style.borderTop = '1px solid #ebeef5';

      var nextBillingLabel = document.createElement('label');
      nextBillingLabel.style.display = 'block';
      nextBillingLabel.style.marginBottom = '6px';
      nextBillingLabel.style.fontWeight = '500';
      nextBillingLabel.textContent = '下次扣款日';
      nextBillingRow.appendChild(nextBillingLabel);

      var nextBillingValue = subscription.next_billing_date || (subscription.payuni_display && subscription.payuni_display.next_billing_date) || '';
      var inputGroup = document.createElement('div');
      inputGroup.style.display = 'flex';
      inputGroup.style.flexWrap = 'wrap';
      inputGroup.style.gap = '8px';
      inputGroup.style.alignItems = 'center';

      var dateInput = document.createElement('input');
      dateInput.type = 'datetime-local';
      dateInput.setAttribute('class', 'el-input__inner');
      dateInput.style.maxWidth = '220px';
      if (nextBillingValue) {
        var d = new Date(nextBillingValue.replace(/-/g, '/').replace(' ', 'T'));
        if (!isNaN(d.getTime())) {
          dateInput.value = d.toISOString().slice(0, 16);
        }
      }
      inputGroup.appendChild(dateInput);

      var saveBillingBtn = document.createElement('button');
      saveBillingBtn.type = 'button';
      saveBillingBtn.setAttribute('class', 'el-button el-button--primary');
      saveBillingBtn.textContent = '儲存';
      saveBillingBtn.addEventListener('click', function () {
        saveNextBillingDate(subscription, dateInput, saveBillingBtn);
      });
      inputGroup.appendChild(saveBillingBtn);

      nextBillingRow.appendChild(inputGroup);
      body.appendChild(nextBillingRow);
    }

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

  function saveNextBillingDate(subscription, dateInputEl, buttonEl) {
    var config = getConfig();
    var apiBase = config.payuniApiBase;
    var nonce = config.nonce;
    if (!apiBase || !nonce) {
      alert('設定遺失，無法儲存');
      return;
    }

    var raw = dateInputEl.value;
    if (!raw || raw.trim() === '') {
      alert('請選擇下次扣款日');
      return;
    }

    var d = new Date(raw);
    if (isNaN(d.getTime())) {
      alert('日期格式不正確');
      return;
    }
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    var h = String(d.getHours()).padStart(2, '0');
    var min = String(d.getMinutes()).padStart(2, '0');
    var sec = String(d.getSeconds()).padStart(2, '0');
    var nextBillingDate = y + '-' + m + '-' + day + ' ' + h + ':' + min + ':' + sec;

    var url = apiBase + 'subscriptions/' + subscription.id + '/next-billing-date';
    var originalText = buttonEl.textContent;
    buttonEl.disabled = true;
    buttonEl.textContent = '儲存中…';

    fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ next_billing_date: nextBillingDate }),
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
          if (typeof window.Notify !== 'undefined' && window.Notify.success) {
            window.Notify.success(msg || '下次扣款日已更新');
          } else {
            alert(msg || '下次扣款日已更新');
          }
          setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          if (typeof window.Notify !== 'undefined' && window.Notify.error) {
            window.Notify.error(msg || '儲存失敗');
          } else {
            alert(msg || '儲存失敗');
          }
        }
      })
      .catch(function (err) {
        var msg = (err && err.message) ? err.message : '網路錯誤，請稍後再試';
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

  var CANCEL_REASON_OPTIONS = [
    { value: 'customer_request', label: '客戶要求' },
    { value: 'too_expensive', label: '價格考量' },
    { value: 'not_using', label: '不再使用' },
    { value: 'switching', label: '改用其他方案' },
    { value: 'other', label: '其他' },
  ];

  function showCancelReasonModal(subscription, buttonEl) {
    var overlay = document.createElement('div');
    overlay.setAttribute('class', 'payuni-cancel-reason-overlay');
    overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:100000;';

    var box = document.createElement('div');
    box.setAttribute('class', 'payuni-cancel-reason-box');
    box.style.cssText = 'background:#fff;padding:20px;border-radius:8px;min-width:280px;box-shadow:0 4px 20px rgba(0,0,0,0.15);';

    var title = document.createElement('div');
    title.style.marginBottom = '12px';
    title.style.fontWeight = '600';
    title.textContent = '請選擇取消原因';
    box.appendChild(title);

    var select = document.createElement('select');
    select.style.cssText = 'width:100%;padding:8px 12px;margin-bottom:16px;border:1px solid #dcdfe6;border-radius:4px;font-size:14px;';
    CANCEL_REASON_OPTIONS.forEach(function (opt) {
      var option = document.createElement('option');
      option.value = opt.value;
      option.textContent = opt.label;
      select.appendChild(option);
    });
    box.appendChild(select);

    var btnRow = document.createElement('div');
    btnRow.style.cssText = 'display:flex;gap:8px;justify-content:flex-end;';

    var backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.setAttribute('class', 'el-button el-button--default');
    backBtn.textContent = '返回';
    backBtn.addEventListener('click', function () {
      document.body.removeChild(overlay);
    });
    btnRow.appendChild(backBtn);

    var confirmBtn = document.createElement('button');
    confirmBtn.type = 'button';
    confirmBtn.setAttribute('class', 'el-button el-button--danger');
    confirmBtn.textContent = '確定取消';
    confirmBtn.addEventListener('click', function () {
      var reason = (select.value || '').trim();
      if (!reason) {
        alert('請選擇取消原因');
        return;
      }
      document.body.removeChild(overlay);
      doCancelSubscription(subscription, buttonEl, reason);
    });
    btnRow.appendChild(confirmBtn);

    box.appendChild(btnRow);
    overlay.appendChild(box);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        document.body.removeChild(overlay);
      }
    });
    document.body.appendChild(overlay);
  }

  function doCancelSubscription(subscription, buttonEl, cancelReason) {
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
      body: JSON.stringify({ cancel_reason: cancelReason }),
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

  function reactivateSubscription(subscription, buttonEl) {
    var config = getConfig();
    var restUrl = config.restUrl;
    var nonce = config.nonce;
    if (!restUrl || !nonce) {
      alert('設定遺失，無法重新啟用');
      return;
    }

    var orderId = subscription.parent_order_id;
    var subscriptionId = subscription.id;
    if (!orderId || !subscriptionId) {
      alert('訂閱資料不完整');
      return;
    }

    if (!window.confirm('確定要重新啟用此訂閱？將恢復自動扣款。')) {
      return;
    }

    var url = restUrl + 'orders/' + orderId + '/subscriptions/' + subscriptionId + '/reactivate';
    var originalText = buttonEl.textContent;
    buttonEl.disabled = true;
    buttonEl.textContent = '啟用中…';

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
        var body = result.body || {};
        var msg = body.message;

        if (result.ok) {
          var successMsg = msg || '訂閱已重新啟用';
          if (typeof window.Notify !== 'undefined' && window.Notify.success) {
            window.Notify.success(successMsg);
          } else {
            alert(successMsg);
          }
          setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          var errMsg = msg || '重新啟用失敗';
          if (typeof window.Notify !== 'undefined' && window.Notify.error) {
            window.Notify.error(errMsg);
          } else {
            alert(errMsg);
          }
        }
      })
      .catch(function (err) {
        var msg = (err && err.message) ? err.message : '網路錯誤，請稍後再試';
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
