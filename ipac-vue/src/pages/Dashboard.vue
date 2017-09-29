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
				userType: localStorage.userType,
				tasksFiltering: {
					user: JSON.parse(localStorage.userDetails),
					is_complete: 0,
					heading: "To-Do Tasks for "+JSON.parse(localStorage.userDetails).first_name + " " + JSON.parse(localStorage.userDetails).last_name,
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
				if (localStorage.userType == 'User') {
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
			fetchProjects() {

			},
			fetchClients() {

			}
		},
		created() {
			this.users = JSON.parse(localStorage.users)

			this.fetchTasks()

			this.$emit("toggleSpinner",true)
			
		}
	}
</script>

<style type="text/css" scoped>

.avatar {
	text-align: center;
}

.avatar i {
	font-size: 140pt
}

</style>

