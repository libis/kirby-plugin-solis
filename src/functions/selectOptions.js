export async function fetchApiOptions(apiEndpoint, language, textKey, valueKey) {
  const apiUrl = apiEndpoint.replace('{language}', language);

  const response = await fetch(`/all-solis-records?api=${encodeURIComponent(apiUrl)}`);
  const apiOptions = await response.json();

  return apiOptions.map(item => ({
    text: item[textKey] || '',
    value: item[valueKey] || ''
  })).sort((a, b) => a.text.localeCompare(b.text));
}
