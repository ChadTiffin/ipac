<template>
	<div>

    	<image-upload-field :api-base="apiBase" v-on:imageListChanged="saveUpload" :field="expenseUpload" :multi="false" upload-msg="+ UPLOAD EXPENSE RECEIPT" upload-type="expense" :no-thumbs="true"></image-upload-field>

    	<table class="table table-striped">
    		<thead>
    			<th>Date Uploaded</th>
    			<th v-if="ownerType == 'client'">Location</th>
    			<th>View</th>
    			<th></th>
    		</thead>
    		<tbody>
    			<tr v-for="expense in expensesWithTokens">
    				<td>{{ expense.updated_at }}</td>
    				<td v-if="ownerType == 'client'">{{ expense.locations.location_name }}</td>
    				<td><a :href="expense.receipt_link" target="_blank">View Receipt</a></td>
    				<td>
    					<button class="btn btn-danger btn-sm" v-on:click="deleteExpense(expense)"><i class="fa fa-times"></i></button>
    				</td>
    			</tr>
    		</tbody>
    	</table>

    	<modal-dialog
            v-if="confirmDialog.visible" 
            :title="confirmDialog.title" 
            :modal-visible="confirmDialog.visible" 
            :confirm-button-text="confirmDialog.buttonText"
            :button-class="confirmDialog.buttonClass"
            v-on:confirm="executeDeleteExpense(confirmDialog.parameters)"
            v-on:closeModal="confirmDialog.visible = false">
            {{ confirmDialog.message }}
        </modal-dialog>

    	<modal-dialog
            v-if="locationSelectDialog.visible" 
            title="Set Client Location for Expense" 
            :modal-visible="locationSelectDialog.visible" 
            confirm-button-text="Save"
            button-class="btn-success"
            v-on:confirm="postExpense"
            v-on:closeModal="locationSelectDialog.visible = false">
            
            <form-group label="Client Location" col-class="col-md-4">
            	<select class="form-control" v-model="locationSelectDialog.fields.location_id">
            		<option v-for="location in locations" :value="location.id">{{ location.location_name }}</option>
            	</select>
            </form-group>

        </modal-dialog>

	</div>

</template>

<script type="text/javascript">
	import ModalDialog from '../components/ModalDialog'
	import bus from '../bus.js'
	import ImageUploadField from '../components/ImageUploadField'
	import FormGroup from '../components/FormGroup'
	import TableList from '../components/TableList'

	export default {
		name: "ExpenseList",
		props: ["ownerType","locations"],
		components: {
			ModalDialog,
			ImageUploadField,
			FormGroup,
			TableList
		},
		data() {
			return {
				expenseUpload: {
                    value: []
                },
                expenses: [],
                expensesWithTokens: [],
                locationSelectDialog : { 
                	visible: false,
                	fields: {}
                },
                confirmDialog: {
                    visible: false,
                    title: "Delete Expense Receipt",
                    buttonText: "Delete",
                    buttonClass: "btn-danger",
                    parameters: "",
                    successFunction: "deleteExpense"
                }
			}
		},
		watch: {
			expenses() {
				let imageList = []

				let vm = this

				this.expenses.forEach(function(expense, index){
					imageList.push(expense.uploads.filename)
				})

				this.getJSON(window.apiBase+"image/get-image-tokens?images="+JSON.stringify(imageList)).then(function(response){

					let mergedExpenses = []
					vm.expenses.forEach(function(expense, index){
						Array.from(response).forEach(function(image, index) { 
							if (image.filename == expense.uploads.filename) {
								expense['receipt_link'] = window.apiBase + "image/serve/" + image.filename+"?token="+image.token
							}
						})

						mergedExpenses.push(expense)
					})

					vm.expensesWithTokens = mergedExpenses;

				})
			}
		},
		methods: {
			deleteExpense(expense) {
				this.confirmDialog.visible = true;
				this.confirmDialog.message = "Are you sure you want to delete this expense?"
				this.confirmDialog.parameters = expense
			},
			executeDeleteExpense(expense) {
				let vm = this

				let payload = {
					id: expense.id
				}

				this.postData(this.apiBase+"expense/delete",payload).then(function(response){
					vm.filterExpenses()
					vm.confirmDialog.visible = false

					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-danger",
						msg: "Expense deleted"
					})
				})
			},
			saveUpload(response, field) {
				let vm = this

				let payload = {
					owner_type: this.ownerType,
					owner_id: this.$route.params.id,
					user_id: "{{{current_user}}}",
					upload_id: response.id,
					submitted_at: "{{{server_now}}}"
				}

				if (this.ownerType == "project") {	
					this.postData(window.apiBase+"expense/save",payload).then(function(response){
						vm.filterExpenses()
					})
				}
				else {
					this.locationSelectDialog.fields = payload
					this.locationSelectDialog.fields['location_id'] = 0
					this.locationSelectDialog.visible = true
				}
			},
			postExpense() {
				let vm = this
				this.postData(window.apiBase+"expense/save",this.locationSelectDialog.fields).then(function(response){
					vm.filterExpenses()
					vm.locationSelectDialog.visible = false
				})
			},
			filterExpenses() {
				let vm = this

				let user_id = JSON.parse(localStorage.userDetails).id

				let filter = JSON.stringify([
					["user_id",user_id],
					["owner_type","project"],
					["owner_id",this.$route.params.id]
				]);

				let order = JSON.stringify(["updated_at","DESC"]);

				this.getJSON(window.apiBase+"expense/get?filter="+filter+"&order="+order).then(function(response){
					vm.expenses = response

				})
			}
		},
		created() {
			this.filterExpenses()
		}
	}
</script>

<style type="text/css" scoped>


</style>