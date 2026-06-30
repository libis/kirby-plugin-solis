import Records from "./components/views/Records.vue";
import relationField from "./components/fields/relationField.vue";
import relationDialog from "./components/fields/relationDialog.vue";
import LanguageDropdownButton from "./components/buttons/LanguageDropdownButton.vue";
import AddMultipleValuesField from "./components/fields/AddMultipleValuesField.vue";
import MultipleRecordOfType from "./components/fields/MultipleRecordOfType.vue";
import DetailOfOneRecord from "./components/views/recordPages/DetailOfOneRecord.vue";
import AddOneRecordView from "./components/views/recordPages/AddOneRecordOfType.vue";
import ListOfRecords from "./components/views/recordPages/ListOfOneRecordType.vue";
import CodeTablesView from "./components/views/CodeTables/CodeTableView.vue";
import ImageUpload from "./components/fields/ImageUpload.vue";
import ImageDialog from "./components/fields/imageUploadDialog.vue";

panel.plugin("libis/solis-records", {
	components: {
		"k-records-view": Records,
		"k-detail-of-one-record-view": DetailOfOneRecord,
		"k-add-one-record-of-type": AddOneRecordView,
		"k-list-of-records-view": ListOfRecords,
		"k-relation-field": relationField,
		"k-dialog-relation": relationDialog,
		"k-add-multiple-values-field": AddMultipleValuesField,
		"k-multiple-records-of-type": MultipleRecordOfType,
		"k-codetables-view": CodeTablesView,
		"k-image-dialog": ImageDialog,
		LanguageDropdownButton
	},
	fields: {
		add_multiple_values_field: AddMultipleValuesField,
		relationDialog: relationField,
		multiple_records_of_type: MultipleRecordOfType,
		imageUpload: ImageUpload
	},
});
