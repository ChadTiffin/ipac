<template>
    <div style="overflow-x: auto;">

    	<table class="table table-striped" :class="{'table-hover': rowClickable}">
            <thead>
                <tr>
                    <th v-for="field in fields">{{field.label}}</th>
                    <th v-if="hasEdit || hasDelete"></th>
                </tr>
            </thead>
            <tbody >
                <tr v-for="record in records" v-on:click="editRecord(record)">
                    <td v-for="field in fields">
                        {{ record[field.key] }}
                    </td>
                    <td v-if="hasEdit || hasDelete">

                        <div class="other-buttons" v-for="button in otherButtons" v-if="otherButtons">
                            <router-link class="btn btn-sm" style="margin-left: 4px;" :to="button.link + record.id" :class="button.class">
                                <i v-if="button.icon" class="fa" :class="button.icon"></i>
                                {{ button.text }}
                            </router-link>
                        </div>

                        <button v-if="hasEdit == true" class="btn btn-default btn-sm" v-on:click="editRecord(record)">
                            <i class="fa fa-pencil"></i>
                        </button>

                        <router-link v-else :to="hasEdit + record.id" class="btn btn-default btn-sm">
                            <i class="fa fa-pencil"></i>
                        </router-link>

                        <button v-if="hasDelete" class="btn btn-danger btn-sm" v-on:click="deleteRecord(record)"><i class="fa fa-remove"></i></button>
                        
                    </td>
                </tr>
                <tr v-if="records.length == 0">
                    <td :colspan="fields.length">No records</td>
                    <td v-if="hasEdit || hasDelete"></td>
                </tr>
            </tbody>
        </table>

        <modal-dialog
            v-if="confirmDialog.visible" 
            :title="confirmDialog.title" 
            :modal-visible="confirmDialog.visible" 
            :confirm-button-text="confirmDialog.buttonText"
            :button-class="confirmDialog.buttonClass"
            v-on:confirm="executeDelete(confirmDialog.parameters)"
            v-on:closeModal="confirmDialog.visible = false">
            {{ confirmDialog.message }}
        </modal-dialog>
    </div>
</template>

<script type="text/javascript">
    import ModalDialog from '../components/ModalDialog'

	export default {
		name: "TableList",
        components: {
            ModalDialog
        },
		props: [
            "records",
            "fields",
            "hasEdit", //if true, its a button that resolves to editRecord method on click, otherwise its a link that contains the root path "hasEdit/{record_id}" (slash is included in hasEdit)
            "hasDelete",
            "otherButtons",
            "recordName",
            "deleteEndpoint",
            "rowClickable"
        ],
        data() {
            return {
                confirmDialog: {
                    visible: false,
                    title: "",
                    buttonText: "Delete",
                    buttonClass: "btn-danger",
                    parameters: "",
                    successFunction: ""
                }
            }
        },
        computed: {
            resolvedRecordName() {
                if (this.recordName)
                    return this.recordName
                else
                    return "Record"
            }
        },
		methods: {
            editRecord(record) {
                
                if (this.hasEdit === true)
                    this.$emit("edit",record)
                else  {
                    if (this.rowClickable)
                        this.$router.push(this.hasEdit + record.id)
                }
                
            },
            deleteRecord(record) {
                this.confirmDialog.title = "Delete "+this.resolvedRecordName
                this.confirmDialog.message = "Are you sure you want to delete this " + this.resolvedRecordName + "?"
                this.confirmDialog.successFunction = "executeDelete"
                this.confirmDialog.buttonText = "Delete"
                this.confirmDialog.buttonClass = "btn-danger"
                this.confirmDialog.parameters = {
                    id: record.id
                }

                this.confirmDialog.visible = true
            },
            executeDelete(parameters) {
                let payload = {
                    id: parameters.id
                }

                let vm = this

                this.postData(window.apiBase+this.deleteEndpoint,payload).then(function(response){
                    vm.$emit("modelChange")
                    vm.confirmDialog.visible = false

                    vm.$emit("updateAlert",{
                        visible: true,
                        class: "alert-danger",
                        msg: "Record deleted"
                    })
                })
            },
        }
	}
</script>

<style type="text/css" scoped>
    .other-buttons {
        display: inline-block;
    }
</style>