
import { fetchApiOptions } from './selectOptions';

// build the data of a field
export async function buildStructure(fieldConfig, language) {
    const structure = {};

    for (const [fieldKey, fieldData] of Object.entries(fieldConfig)) {
        // if the field is a select option get the options out of an other function because an api call needs to ben done and add it to the field
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
        // if the value can be added multple tipes give the needed field via fields in the object
        else if (fieldData.type === 'add_multiple_values_field' && fieldData.fields) {
            const nestedStructure = await buildStructure(fieldData.fields, language);

            structure[fieldKey] = {
                ...fieldData,
                fields: nestedStructure, 
            };
        }
        // simple fields just give the info
        else {
            structure[fieldKey] = fieldData;
        }
    }
    return structure;
}
