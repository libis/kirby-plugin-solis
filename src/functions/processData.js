export function sanitizeText(value) {
  if (typeof value !== 'string') return value;
  return value
    .replace(/(&lt;|<)\/?script[\s\S]*?(&gt;|>)/gi, '')
    .replace(/(&amp;lt;|&lt;)\/?script[\s\S]*?(&amp;gt;|&gt;)/gi, '')
    .replace(/\son\w+\s*=\s*(['"][^'"]*['"]|[^\s>]+)/gi, '')
    .replace(/javascript:/gi, '');
}


export function validateData(data, fields, t = (k) => k) {
  const errors = [];

  const fieldEntries = Array.isArray(fields)
    ? fields.map((field, index) => [index, field])
    : Object.entries(fields);

  const addError = (field, message) => {
    const label = field?.name ?? field?.label ?? t('libis.solis.error.unknown.field.name');
    errors.push({ label, message: String(message) });
  };

  for (const [, field] of fieldEntries) {
    const value = data?.[field.name];
    const label = field.label || field.name;

    // Required check 
    const isRequired =
      field.min === 1 || field.required === true || field.isRequired === true;

    if (isRequired && (value === null || value === '' || value === undefined)) {
      addError(field, t("libis.solis.error.required", { label: label }));
    }

    // Min/Max check voor arrays
    if (Array.isArray(value)) {
      const minOption = field.componentsOptions?.min;
      const maxOption = field.componentsOptions?.max;
      const length = value.length;

      if (minOption != null && !Number.isNaN(Number(minOption)) && length < Number(minOption)) {
        addError(field, t("libis.solis.error.min.record", { label: label, min: Number(minOption) }));
      }
      if (maxOption != null && !Number.isNaN(Number(maxOption)) && length > Number(maxOption)) {
        addError(field, t("libis.solis.error.max.record", { label: label, max: Number(maxOption) }));
      }
    }

    // Number check
    if (
      (field.type === 'number' || field.type === 'number-field') &&
      value !== '' && value !== null && value !== undefined
    ) {
      const num = Number(value);
      if (Number.isNaN(num)) {
        addError(field, t("libis.solis.error.valid.number", { label: label }));
      } else {
        const min = field.componentsOptions?.min;
        const max = field.componentsOptions?.max;
        if (min != null && num < Number(min)) {
          addError(field, t("libis.solis.error.number.min", { label: label, min: min }));
        }
        if (max != null && num > Number(max)) {
          addError(field, t("libis.solis.error.number.max", { label: label, max: max }));
        }
      }
    }

    // Email check
    if ((field.type === 'email' || field.type === 'email-field') && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(String(value))) {
        addError(field, t("libis.solis.error.valid.email", { label: label }));
      }
    }

    // URL check
    if ((field.type === 'url' || field.type === 'url-field') && value) {
      try {
        new URL(String(value));
      } catch {
        addError(field, t("libis.solis.error.valid.url", { label: label }));
      }
    }
  }

  return errors.length ? errors : true;
}

export function formatData(data, fields) {
  const formattedData = {};

  const fieldEntries = Array.isArray(fields)
    ? fields.map((field, index) => [index, field])
    : Object.entries(fields);

  for (const [key, field] of fieldEntries) {
    const fieldName = field.name;
    let value = data[fieldName];

    if (value === undefined) continue;

    // Sanitize text fields
    if ((field.type === 'text-field' || field.type === 'text') && typeof value === 'string') {
      value = sanitizeText(value);
    }

    if (Array.isArray(value)) {
      const isMultipleValuesEntity =
        (field.type === 'entity' && field.subType === 'add-multiple-values-field') ||
        field.type === 'add_multiple_values_field' || (field.type === 'entity' && field.subType === 'relation-field') ||
        field.type === 'relation_field';

      const hasMaxConstraint = field.componentsOptions?.max != null;
      const multipleRecordOfType = (field.type === 'entity' && field.subType === 'multiple-records-of-type') || field.type === 'multiple_records_of_type';
      const imageSelectField = field.type === 'entity' && field.subType === 'image-select-field';

      if (isMultipleValuesEntity || (hasMaxConstraint && !(multipleRecordOfType || imageSelectField))) {
        const ids = value
          .map(item => (item && typeof item === 'object' && item.id ? { id: item.id } : null))
          .filter(Boolean);

        formattedData[fieldName] = field.componentsOptions?.max === 1 ? ids[0] || null : ids;
      }
      else if (multipleRecordOfType) {
        if (field.sendType && field.sendType == 'strings') {
          if (field.valueSelector == null) {
            const cleanedValue = value
              .map(val => (typeof val === "string" ? val.trim() : val))
              .filter(val => val !== "" && val != null);

            if (cleanedValue.length > 0) {
              formattedData[fieldName] = cleanedValue;
            }
            else {
              formattedData[fieldName] = "";
            }

          }
          else {
            formattedData[fieldName] = value.map(obj => Object.values(obj)[0]);
          }
        }
        else {
          formattedData[fieldName] = value
            .map(item => (item && typeof item === 'object' && item.id ? { id: item.id } : null))
            .filter(Boolean);
        }
      }
      else if (imageSelectField) {
        if (field.componentsOptions?.max === 1) {
          formattedData[fieldName] = formattedData[fieldName] = value[0].id;
        }
        else {
          formattedData[fieldName] = value.map(obj => Object.values(obj)[0]);
        }
      }
      else {
        formattedData[fieldName] = value;
      }
      continue;
    }
    else if (((field.type === 'entity' && field.subType === 'relation-field') ||
      field.type === 'relation_field') && field.componentsOptions?.max == 1) {
      formattedData[fieldName] = { id: value.id };
      continue;
    }
    else if (field.type == 'select-field' && field.componentsOptions?.entity) {
      formattedData[fieldName] = { 'id': value };
    }
    else {
      const isSelectEntity =
        (field.entity === true && field.type === 'select') ||
        (
          field.type === 'entity' &&
          field.subType === 'multiple-records-of-type' &&
          field.componentsOptions?.inputType === 'select'
        );

      if (isSelectEntity && typeof value === 'string') {
        const lastPart = value.split('/').filter(Boolean).pop();
        formattedData[fieldName] = lastPart ? { id: lastPart } : null;
      }
      else {
        formattedData[fieldName] = value;
      }
      continue;
    }
  }
  return formattedData;
}
