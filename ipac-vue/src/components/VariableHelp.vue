<template>
	<modal-dialog
        v-if="modalVisible" 
        title="Template Variable Reference" 
        :modal-visible="modalVisible" 
        :submit-visible="false"
        v-on:closeModal="close">
        
        <table class="table table-striped table-condensed">
        	<thead>
	        	<tr>
	        		<th>Object</th>
	        		<th>Variable Name</th>
        		</tr>
        	</thead>
        	<tbody>
        		<tr>
        			<td :rowspan="client.length+1">client.</td>
        		</tr>
        		<tr v-for="field in client">
        			<td>{{ field }}</td>
        		</tr>
                <tr>
                    <td :rowspan="location.length+1">location.</td>
                </tr>
                <tr v-for="field in location">
                    <td>{{ field }}</td>
                </tr>
        		<tr>
        			<td :rowspan="company.length+1">company.</td>
        		</tr>
        		<tr v-for="field in company">
        			<td>{{field}}</td>
        		</tr>
                <tr>
                    <td :rowspan="user.length+1">user.</td>
                </tr>
                <tr v-for="field in user">
                    <td>{{field}}</td>
                </tr>
        		<tr>
        			<td :rowspan="date.length+1">date</td>
        		</tr>
                <tr v-for="field in date">
                    <td>{{ field }}</td>
                </tr>
        	</tbody>
        </table>

        <p style="margin-top: 10px;" v-pre><strong>Example usage:</strong> {{company.email}}</p>

    </modal-dialog>
</template>

<script type="text/javascript">
	import ModalDialog from "./ModalDialog"

	export default {
		name: "VarHelp",
		components: {
			ModalDialog
		},
		props: ["modalVisible"],
		data() {
			return {
				visible: false,
				client: ["address","city","company","contact_name","email","postal_code","primary_phone","province"],
                location: ["location_name","address","city","province","postal_code", "phone"],
				company: ["address","city","company_name","email","personal_name","postal_code","province","websites"],
                user: ["first_name","last_name","email"],
                date: ["report_issued","audit_performed"]
			}
		},
		methods: {
			close() {
				this.$emit("close")
			}
		}
	}
</script>

<style type="text/css" scoped>

</style>