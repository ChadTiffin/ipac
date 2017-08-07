<template>

	<datepicker :name="name" :value="valueDate" v-on:input="input($event)" :input-class="extraClasses" format="yyyy/MM/dd"></datepicker>

</template>

<script type="text/javascript">
	import Datepicker from 'vuejs-datepicker'

	export default {
		name: "DateField",
		props: ["name","extraClasses","value"],
		components: {
			Datepicker
		},
		data() {
			return {
				valueDate: null
			}
		},
		watch: {
			value() {
				
				//correct for timezone
				this.valueDate = new Date(this.value)

				let offset = this.valueDate.getTimezoneOffset()
				let minutes = this.valueDate.getMinutes();

				let difference = offset - minutes

				this.valueDate.setMinutes(difference)
			}
		},
		methods: {
			input(value) {

				let date_string = value.getFullYear()+"-"+(value.getMonth()+1)+"-"+value.getDate()

				this.$emit("input",date_string)
				this.$emit("change")
			}
		}
	}
</script>

<style type="text/css">
	.vdp-datepicker .vdp-datepicker__calendar .cell {
		line-height: 30px;
		height: 30px;
	}

	.vdp-datepicker .vdp-datepicker__calendar {
		width: 250px;
	}
</style>