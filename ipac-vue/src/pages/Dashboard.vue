<template>
<section>

	<div class="panel panel-default">
		<div class="panel-heading">
			Tasks
		</div>
		<div class="panel-body">

			<div class="row">
				<div class="col-md-3">
					<div class="well">
						<h3>Filter</h3>

						<h4>User</h4>
						<button-group
							vertical="true" 
							:buttons="taskUserFilteringButtons" 
							:full-width="true"
							v-model="tasksFiltering.user.id"
							v-on:change="fetchTasks"
							v-if="userType != 'User'">
						</button-group>

						<h4>Status</h4>
						<button-group
							vertical="true" 
							:buttons="tasksFiltering.statusFilterButtons" 
							:full-width="true"
							v-model="tasksFiltering.statusValue"
							v-if="userType != 'User'">
						</button-group>

						<!--<h4>Project/Client</h4>

						<select class="form-control">
							<option>All Clients/Projects</option>
							<optgroup label="Clients">

							</optgroup>
							<optgroup label="Projects">
								
							</optgroup>
						</select>-->
					</div>

				</div>
				<div class="col-md-9">

					<task-list :show-owner="true" :heading="tasksFiltering.heading" :include-new-button="true" :tasks="filteredTasks" :editable="true"></task-list>
				</div>

			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			Your Expenses ({{ fullName }}) from This Month
		</div>

		<div style="overflow-x: auto;">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date Uploaded</th>
						<th>User</th>
						<th>Client/Project</th>
						<th>Location</th>
						<th>Processed</th>
						<th>View</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="expense in expensesWithTokens" :key="expense.id">
						<td>{{ formatDate(expense.submitted_at,'short') }}</td>
						<td>{{ expense.users.first_name }} {{ expense.users.last_name }}</td>
						<td>{{ expense.owner_type == "client" ? expense.clients.company : expense.projects.project_name }}</td>
						<td>{{ expense.locations && expense.owner_type == 'client' ? expense.locations.location_name : '' }}</td>
						<td>
							<i class="fa" :class="{'fa-check': expense.processed == 1, 'fa-times': expense.processed == 0}"></i>
						</td>
						<td><a :href="expense.receipt_link" target="_blank">View Receipt</a></td>
					</tr>
					<tr v-if="expensesWithTokens.length == 0">
						<td colspan="6">
							No Expenses found
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>


</section>
</template>

<script type="text/javascript">
	import TaskList from '../components/TaskList'
	import ButtonGroup from '../components/ButtonGroup'

	export default {
		name: "Dashboard",
		props: [],
		components: {
			TaskList,
			ButtonGroup
		},
		data () {
			return {
				tasks: [], 
				users: [],
				expenses: [],
				userType: localStorage.userType ? localStorage.userType : null,
				expensesWithTokens: [],
				tasksFiltering: {
					user: {},
					is_complete: 0,
					heading: "",
					userFilterButtons: [],
					statusFilterButtons: [
                		{
                			text: "To-Do",
                			value: "To-Do"
                		},
                		{
                			text: "Needs Billing",
                			value: "Needs Billing"
                		},
                		{
                			text: "Completed",
                			value: "Completed"
                		}
                	],
                	statusValue: "To-Do",
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
								expense['receipt_link'] = window.apiBase + "image/serve/" + image.token + "/" + image.filename

								expense.updated_at = moment(expense.updated_at).format("YYYY-MM-DD")
							}
						})

						mergedExpenses.push(expense)
					})

					vm.expensesWithTokens = mergedExpenses;

				})
			}
		},
		computed: {
			filteredTasks() {
				let filtered = []

				let vm = this

				if (this.tasksFiltering.statusValue == "To-Do") {
					this.tasks.forEach(function(task, index) {
						if (task.is_complete == 0)
							filtered.push(task)
					})
				}
				else if (this.tasksFiltering.statusValue == "Needs Billing") {
					this.tasks.forEach(function(task, index) {
						if (task.is_complete == 1 && task.billed == 0 & task.billable == 1)
							filtered.push(task)
					})
				}
				else if (this.tasksFiltering.statusValue == "Completed") {
					this.tasks.forEach(function(task, index) {
						if (task.is_complete == 1 && task.billable == 0 || task.is_complete == 1 && task.billable == 1 && task.billed == 1)
							filtered.push(task)
					})
				}

				return filtered
			},
			taskUserFilteringButtons () {
				let buttons = []
				if (this.userType == 'User') {
					this.tasksFiltering.userFilterButtons = []
				}
				else {
					
					if (this.users) {
						this.users.forEach(function(user, index) {
							buttons.push({
								text: user.first_name+" "+user.last_name,
								value: user.id
							})
						})
					}
				}

				return buttons
			}
		},
		methods: {
			fetchTasks() {
				let vm = this

				vm.$emit("toggleSpinner",true)

				let user_id = this.tasksFiltering.user.id

				let filterUserName = ""
				this.users.forEach(function(user, index) {
					if (user_id == user.id)
						filterUserName = user.first_name + " " + user.last_name
				})

				this.tasksFiltering.heading = this.tasksFiltering.statusValue + " Tasks for " + filterUserName

				this.getJSON(window.apiBase + "task/filter/user/"+user_id).then(function(response){
					vm.tasks = response

					vm.$emit("toggleSpinner",false)
				})
			},
			filterExpenses() {
				let vm = this

				vm.$emit("toggleSpinner",true)

				let user_id = localStorage.userDetails ? JSON.parse(localStorage.userDetails).id : 0

				let first_of_month = new Date(new Date().getFullYear(), new Date().getMonth(), 1)

				first_of_month = this.formatDate(first_of_month, "short");

				let filter = [
					["expenses.user_id",user_id],
					["expenses.submitted_at >=",first_of_month]
				];

				filter = JSON.stringify(filter)

				let order = JSON.stringify(["submitted_at","DESC"]);

				this.getJSON(window.apiBase+"expense/get?filters="+filter+"&order="+order).then(function(response){
					vm.expenses = response

					vm.$emit("toggleSpinner",false)

				})
			},
			fetchProjects() {

			},
			fetchClients() {

			}
		},
		created() {
			this.users = localStorage.users ? JSON.parse(localStorage.users) : []

			this.fetchTasks()
			this.filterExpenses()

			this.tasksFiltering.heading = "To-Do Tasks for "+ this.fullName
			this.tasksFiltering.user = localStorage.userDetails ? JSON.parse(localStorage.userDetails) : null

			this.$emit("toggleSpinner",true)
			
		}
	}
</script>

<style type="text/css" scoped>

section {
	background-color: transparent;
}

.avatar {
	text-align: center;
}

.avatar i {
	font-size: 140pt
}

</style>

