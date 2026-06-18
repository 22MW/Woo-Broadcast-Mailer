function getConfig() {
  return window.pbmEmailEditor || {};
}

export async function emailEditorRequest(action, payload = {}) {
  const config = getConfig();
  const params = new URLSearchParams();

  params.append('action', action);
  params.append('nonce', config.nonce || '');

  Object.entries(payload).forEach(([key, value]) => {
    params.append(key, typeof value === 'string' ? value : JSON.stringify(value));
  });

  const response = await fetch(config.ajaxUrl || window.ajaxurl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: params.toString(),
  });

  const data = await response.json();
  if (!data || !data.success) {
    throw new Error(data?.data?.message || 'No se pudo procesar la solicitud.');
  }

  return data.data || {};
}
