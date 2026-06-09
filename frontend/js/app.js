// ============================================================
//  DCRS — Global JavaScript
//  File: frontend/js/app.js
//  UPDATED: Live notification polling + toast popup
// ============================================================

const DCRS = (() => {

  // ── Toast notification ──────────────────────────────────────
  const Toast = {
    container: null,

    init() {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      this.container.style.cssText = `
        position: fixed; bottom: 24px; right: 24px;
        z-index: 9999; display: flex; flex-direction: column; gap: 10px;
      `;
      document.body.appendChild(this.container);
    },

    show(message, type = 'info', duration = 4500) {
      const colors = {
        info    : { bg: '#1a3a5c', icon: '🔔' },
        success : { bg: '#0f6e56', icon: '✅' },
        warning : { bg: '#ba7517', icon: '⚠️' },
        error   : { bg: '#a32d2d', icon: '❌' },
      };
      const c = colors[type] || colors.info;

      const toast = document.createElement('div');
      toast.style.cssText = `
        background: ${c.bg}; color: #fff;
        padding: 12px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        max-width: 320px; animation: slideInToast 0.3s ease;
        cursor: pointer;
      `;
      toast.innerHTML = `<span style="font-size:18px;flex-shrink:0;">${c.icon}</span><span>${message}</span>`;
      toast.onclick = () => toast.remove();

      this.container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => toast.remove(), 400);
      }, duration);
    }
  };

  // ── Notification polling ────────────────────────────────────
  const Notifications = {
    apiUrl       : null,
    lastCount    : -1,
    pollInterval : 10000, // 10 seconds

    init(apiUrl) {
      this.apiUrl = apiUrl;
      this.bindEvents();
      this.fetchAndUpdate();
      setInterval(() => this.fetchAndUpdate(), this.pollInterval);
    },

    fetchAndUpdate() {
      if (!this.apiUrl) return;

      fetch(`${this.apiUrl}?action=count`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
          if (!data.success) return;
          const count = data.unread_count;

          // If count increased since last poll → new notification arrived
          if (this.lastCount !== -1 && count > this.lastCount) {
            const diff = count - this.lastCount;
            Toast.show(
              `You have ${diff} new notification${diff > 1 ? 's' : ''}!`,
              'info'
            );
            // Refresh the dropdown list
            this.loadList();
          }

          this.lastCount = count;
          this.updateBadge(count);
        })
        .catch(() => {});
    },

    loadList() {
      if (!this.apiUrl) return;
      fetch(`${this.apiUrl}?action=get`, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
          if (data.success) this.renderList(data.notifications);
        })
        .catch(() => {});
    },

    renderList(notifs) {
      const list = document.getElementById('notifList');
      if (!list) return;

      if (!notifs || notifs.length === 0) {
        list.innerHTML = `
          <div style="padding:1rem;text-align:center;
            color:var(--text-muted);font-size:13px;">No notifications</div>`;
        return;
      }

      const iconMap = {
        submitted : '📝', assigned : '👤', updated : '🔄',
        resolved  : '✅', closed   : '🔒', comment  : '💬',
      };

      list.innerHTML = notifs.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}">
          <span class="notif-icon">${iconMap[n.type] || '🔔'}</span>
          <div>
            <p>${n.message}</p>
            <small>${timeAgo(n.created_at)}</small>
          </div>
        </div>
      `).join('');
    },

    updateBadge(count) {
      const badge = document.getElementById('notifCount');
      if (!badge) return;
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    },

    bindEvents() {
      const btn      = document.getElementById('notifBtn');
      const dropdown = document.getElementById('notifDropdown');
      const markAll  = document.getElementById('markAllRead');

      if (btn && dropdown) {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const isOpen = dropdown.classList.toggle('open');
          if (isOpen) this.loadList();
        });
        document.addEventListener('click', () => {
          if (dropdown) dropdown.classList.remove('open');
        });
        dropdown.addEventListener('click', e => e.stopPropagation());
      }

      if (markAll) {
        markAll.addEventListener('click', () => {
          fetch(`${this.apiUrl}?action=mark_all_read`, { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
              if (data.success) {
                this.updateBadge(0);
                this.lastCount = 0;
                document.querySelectorAll('.notif-item.unread')
                  .forEach(el => el.classList.remove('unread'));
              }
            });
        });
      }
    }
  };

  // ── Modal system ────────────────────────────────────────────
  const Modal = {
    open(modalId) {
      const el = document.getElementById(modalId);
      if (el) el.classList.add('open');
    },
    close(modalId) {
      const el = document.getElementById(modalId);
      if (el) el.classList.remove('open');
    },
    init() {
      document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
          if (e.target === overlay) overlay.classList.remove('open');
        });
      });
      document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
          btn.closest('.modal-overlay').classList.remove('open');
        });
      });
      document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
          e.preventDefault();
          Modal.open(trigger.dataset.modal);
        });
      });
    }
  };

  // ── Progress slider live label ──────────────────────────────
  const ProgressSlider = {
    init() {
      document.querySelectorAll('.progress-slider').forEach(slider => {
        const label = document.getElementById(slider.dataset.label);
        const bar   = document.getElementById(slider.dataset.bar);
        const update = () => {
          if (label) label.textContent = slider.value + '%';
          if (bar)   bar.style.width   = slider.value + '%';
        };
        slider.addEventListener('input', update);
        update();
      });
    }
  };

  // ── Auto-dismiss alerts ─────────────────────────────────────
  const Alerts = {
    init(ms = 5000) {
      document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
          alert.style.transition = 'opacity 0.5s';
          alert.style.opacity = '0';
          setTimeout(() => alert.remove(), 500);
        }, ms);
      });
    }
  };

  // ── Confirm dialogs ─────────────────────────────────────────
  const Confirm = {
    init() {
      document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
          if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
      });
    }
  };

  // ── Filter auto-submit ──────────────────────────────────────
  const AutoFilter = {
    init() {
      document.querySelectorAll('.auto-filter').forEach(select => {
        select.addEventListener('change', () => {
          select.closest('form').submit();
        });
      });
    }
  };

  // ── Utility: time ago ───────────────────────────────────────
  function timeAgo(dateStr) {
    const now  = new Date();
    const past = new Date(dateStr);
    const diff = Math.floor((now - past) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff / 60)   + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    return Math.floor(diff / 86400) + ' days ago';
  }

  // ── Init ────────────────────────────────────────────────────
  function init(config = {}) {
    Toast.init();
    Modal.init();
    ProgressSlider.init();
    Alerts.init();
    Confirm.init();
    AutoFilter.init();

    if (config.notifApiUrl) {
      Notifications.init(config.notifApiUrl);
    }
  }

  return { init, Modal, Toast, Notifications, timeAgo };

})();

// Inject toast keyframe animation
const style = document.createElement('style');
style.textContent = `
  @keyframes slideInToast {
    from { transform: translateX(100px); opacity: 0; }
    to   { transform: translateX(0);     opacity: 1; }
  }
`;
document.head.appendChild(style);

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
  DCRS.init({ notifApiUrl: window.DCRS_NOTIF_URL || null });
});
