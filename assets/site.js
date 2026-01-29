(() => {
  const isGame = window.location.pathname.includes('/game/');
  const base = isGame ? '..' : '.';

  const $ = (sel) => document.querySelector(sel);

  const auth = $('#auth');
  const backdrop = $('#backdrop');
  const openAuthBtn = $('#open-auth');
  const closeAuthBtn = $('#close-auth');
  const tabBtns = Array.from(document.querySelectorAll('.tab-btn'));
  const loginForm = $('#login-form');
  const registerForm = $('#register-form');
  const googleLogin = $('#google-login');

  function setRedirectFields() {
    const redirectVal = window.location.pathname + window.location.search;
    [loginForm, registerForm].forEach((form) => {
      if (!form) return;
      let inp = form.querySelector('input[name="redirect"]');
      if (!inp) {
        inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'redirect';
        form.appendChild(inp);
      }
      inp.value = redirectVal;

      const action = form.getAttribute('action') || '';
      if (action.startsWith('auth/')) {
        form.setAttribute('action', base + '/' + action);
      }
    });

    if (googleLogin) {
      const url = new URL(googleLogin.getAttribute('href') || (base + '/oauth/google_start.php'), window.location.href);
      url.searchParams.set('redirect', redirectVal);
      googleLogin.setAttribute('href', url.pathname + '?' + url.searchParams.toString());
    }
  }

  function openAuth() {
    if (!auth || !backdrop) return;
    setRedirectFields();
    auth.classList.add('sidebar-open');
    auth.setAttribute('aria-hidden', 'false');
    backdrop.hidden = false;
  }

  function closeAuth() {
    if (!auth || !backdrop) return;
    auth.classList.remove('sidebar-open');
    auth.setAttribute('aria-hidden', 'true');
    backdrop.hidden = true;
  }

  function setTab(tab) {
    tabBtns.forEach((btn) => btn.classList.toggle('tab-active', btn.dataset.tab === tab));
    if (loginForm) loginForm.classList.toggle('hidden', tab !== 'login');
    if (registerForm) registerForm.classList.toggle('hidden', tab !== 'register');
  }

  if (openAuthBtn) openAuthBtn.addEventListener('click', openAuth);
  if (closeAuthBtn) closeAuthBtn.addEventListener('click', closeAuth);
  if (backdrop) backdrop.addEventListener('click', closeAuth);
  tabBtns.forEach((btn) => btn.addEventListener('click', () => setTab(btn.dataset.tab)));

  async function refreshMe() {
    try {
      const res = await fetch(base + '/auth/me.php', { cache: 'no-store' });
      const data = await res.json();
      if (!data || data.ok !== true) return;

      if (data.logged_in) {
        if (openAuthBtn) {
          openAuthBtn.outerHTML = `<a class="topbar-link" href="${base}/auth/logout.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}">Logout (${String(data.user?.username || 'User')})</a>`;
        }
      }
    } catch (e) {
      // ignore
    }
  }

  refreshMe();
})();
