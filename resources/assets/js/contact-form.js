(function (window) {
  const TOKEN_URL = "/forms/get-csrf-token.php";
  const SUBMIT_URL = "/forms/contact.php";

  const readToken = (form) => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    const input = form?.querySelector('input[name="csrf_token"], input[name="_token"]');
    return String(input?.value || meta?.getAttribute("content") || "").trim();
  };

  const writeToken = (form, token) => {
    if (!form || !token) return;
    const ensure = (name, id) => {
      let field = form.querySelector(`input[name="${name}"]`);
      if (!field) {
        field = document.createElement("input");
        field.type = "hidden";
        field.name = name;
        if (id) field.id = id;
        form.prepend(field);
      }
      field.value = token;
    };
    ensure("csrf_token", "csrf_token");
    ensure("_token");
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) meta.setAttribute("content", token);
  };

  const fetchToken = async () => {
    const response = await fetch(TOKEN_URL, {
      cache: "no-store",
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });
    if (!response.ok) {
      throw new Error("token-http");
    }
    const data = await response.json();
    if (!data?.token) {
      throw new Error("token-empty");
    }
    return data.token;
  };

  const ensureToken = async (form) => {
    let token = readToken(form);
    if (!token) {
      token = await fetchToken();
    }
    writeToken(form, token);
    return token;
  };

  const friendlyError = (status, text) => {
    const plain = String(text || "").trim();
    if (status === 419 || /token mismatch|page expired|csrf|security token/i.test(plain)) {
      return "Security token expired. Please refresh and try again.";
    }
    if (plain && plain.length < 240 && !plain.startsWith("<") && !plain.includes("</")) {
      return plain;
    }
    if (status === 429) return "Too many requests. Please try again later.";
    if (status >= 500) return "Server error. Please try again later.";
    return "Error sending message. Please try again.";
  };

  const postForm = async (form, retry) => {
    const token = await ensureToken(form);
    const formData = new FormData(form);
    formData.set("csrf_token", token);
    formData.set("_token", token);
    const response = await fetch(SUBMIT_URL, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
      headers: {
        Accept: "text/plain",
        "X-CSRF-TOKEN": token,
        "X-Requested-With": "XMLHttpRequest",
      },
    });
    const text = await response.text();
    if (response.status === 419 && retry !== false) {
      writeToken(form, await fetchToken());
      return postForm(form, false);
    }
    return {
      ok: response.ok && text.trim() === "OK",
      status: response.status,
      text,
      message: friendlyError(response.status, text),
    };
  };

  window.MeetAjForms = {
    readToken,
    writeToken,
    fetchToken,
    ensureToken,
    postForm,
    friendlyError,
  };
})(window);
