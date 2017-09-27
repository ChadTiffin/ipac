<template>

	<date-picker v-model="valueDate" width="100%" lang="en" v-on:input="input($event)" ></date-picker>

</template>

<script type="text/javascript">
	import DatePicker from 'vue2-datepicker'

	export default {
		name: "DateField",
		props: ["value"],
		components: {
			DatePicker
		},
		data() {
			return {
				valueDate: null
			}
		},
		watch: {
			value() {
				this.setValueDate()
				
			}
		},
		methods: {
			setValueDate() {
				//correct for timezone
				this.valueDate = new Date(this.value)

				let offset = this.valueDate.getTimezoneOffset()
				let minutes = this.valueDate.getMinutes();

				let difference = offset - minutes

				this.valueDate.setMinutes(difference)

			},
			input(value) {

				let date_string = ""
				if (value != "")
					date_string = value.getFullYear()+"-"+(value.getMonth()+1)+"-"+value.getDate()

				this.$emit("input",date_string)
				this.$emit("change")
			}
		},
		created() {
			this.setValueDate()
		}
	}
</script>

<style type="text/css" scoped>
	.datepicker {
		width: 100%;
	}
</style>