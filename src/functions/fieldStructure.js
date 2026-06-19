
import { fetchApiOptions } from './selectOptions';

export async function buildStructure(fieldConfig, language) {
    const structure = {};

    for (const [fieldKey, fieldData] of Object.entries(fieldConfig)) {
        if (fieldData.type === 'select' && fieldData.apiEndpoint) {
            const options = await fetchApiOptions(
                fieldData.apiEndpoint,
                language,
                fieldData.textValue,
                fieldData.valueValue
            );

            structure[fieldKey] = {
                ...fieldData,
                options,
                entity: !!fieldData.entity
            };
        }
        else if (fieldData.type === 'add_multiple_values_field' && fieldData.fields) {
            const nestedStructure = await buildStructure(fieldData.fields, language);

            structure[fieldKey] = {
                ...fieldData,
                fields: nestedStructure, 
            };
        }
        else {
            structure[fieldKey] = fieldData;
        }
    }
    return structure;
}
