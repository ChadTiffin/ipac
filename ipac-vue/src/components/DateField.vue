<template>
	<div class="datepicker-wrapper" v-click-outside="closeCalendar">
		<div class="datepicker-input-wrapper">
			<input type="text" :class="classes" class="form-control" :value="selectedFullDate" 
			v-on:focus="calendarOpen = true" placeholder="YYYY-MM-DD">
			
			<i v-if="selectedFullDate" v-on:click="clearDate" class="fa fa-times"></i>
			<i v-else v-on:click="calendarOpen = true" class="fa fa-calendar"></i>
		</div>

		<div class="datepicker-calendar" v-if="calendarOpen"  tabindex="-1">
			<div class="datepicker-header-controls">
				<button type="button" v-on:click="decrement">&laquo;</button>
				<button type="button" v-on:click="changeMode">{{monthNames[selectedMonth-1]}} {{selectedYear}}</button>
				<button type="button" v-on:click="increment">&raquo;</button>
			</div> 
			<div v-if="mode == 'years'" class="datepicker-years">
				<div 
					class="datepicker-year-selecter" 
					v-for="year in yearRange"
					v-on:click="selectYear(year)">
					{{year}}
				</div>
			</div>
			<div v-else-if="mode == 'months'" class="datepicker-months">
				<div 
					class="datepicker-month-of-year" 
					v-for="n in 12"
					v-on:click="selectMonth(n)">
					{{monthNames[n-1].substring(0,3)}}
				</div>
			</div>
			<div v-else class="datepicker-days">
				<div class="datepicker-days-of-week-headings">
					<span>Sun</span>
					<span>Mon</span>
					<span>Tue</span>
					<span>Wed</span>
					<span>Thu</span>
					<span>Fri</span>
					<span>Sat</span>
				</div>
				<div class="datepicker-days-of-month">
					<div 
						class="datepicker-other-month-day datepicker-day-button" 
						v-for="n in (startingDayDayofWeek(selectedMonth,selectedYear))"
						v-on:click="decrement">
						{{n+startingDay(selectedMonth,selectedYear)}}
					</div><div 
						class="datepicker-in-month-day datepicker-day-button" 
						:class="{'selected-day': selectedDay == n}"
						v-for="n in numberDaysInMonth(selectedMonth, selectedYear)"
						v-on:click="selectDay(n)">
						{{n}}
					</div><div 
						class="datepicker-other-month-day datepicker-day-button" 
						v-for="n in (6-endingDayDayofWeek(selectedMonth,selectedYear))"
						v-on:click="increment">
						{{n}}
					</div>
				</div>

			</div>
			<div v-on:click="goToToday" class="datepicker-today-button">Today</div>
		</div>
	</div>
</template>

<script type="text/javascript">

	export default {
		name: "DateField",
		props: [
			"classes",
			"value",
			"initialDate" //accepts a string date in format of YYYY-MM-DD or string of 'today'
		],
		data() {
			return {
				mode: "days",
				calendarOpen: false,
				selectedYear: 2018,
				selectedMonth: 1,
				selectedDay: 1,
				clearedDate: false,
				monthNames: [
					"January",
					"February",
					"March",
					"April",
					"May",
					"June",
					"July",
					"August",
					"September",
					"October",
					"November",
					"December"
				]
			}
		},
		watch: {
			value() {
				this.setValue()
			}
		},
		computed: {
			selectedMonthString() {
				if (typeof this.selectedMonth == "string") {

					if (this.selectedMonth.length == 1)
						return "0"+this.selectedMonth
					else
						return this.selectedMonth
				}
				else {
					if (this.selectedMonth < 10) {
						return "0"+this.selectedMonth
					}
					else
						return this.selectedMonth
				}
			},
			selectedDayString() {
				if (typeof this.selectedDay == "string") {

					if (this.selectedDay.length == 1)
						return "0"+this.selectedDay
					else
						return this.selectedDay
				}
				else {
					if (this.selectedDay < 10)
						return "0"+this.selectedDay
					else
						return this.selectedDay
				}
			},
			yearRange() {
				let yearRange = []

				for (var y = this.selectedYear-5; y <= this.selectedYear+6; y++) {
					yearRange.push(y)
				}

				return yearRange
			},
			selectedFullDate() {

				if (this.clearedDate || (this.selectedYear == 0 && this.selectedMonthString == 0 && this.selectedDayString == 0))
					return null
				else
					return this.selectedYear+'-'+this.selectedMonthString+'-'+this.selectedDayString
			}
		},
		methods: {
			setValue() {
				//split by dashes
				if (this.value) {

					if (this.value == "0000-00-00" || this.value == "0-00-00")
						this.clearedDate = true

					let segments = this.value.split("-")

					if (segments.length == 3) {

						this.selectedYear = parseInt(segments[0])
						this.selectedMonth = parseInt(segments[1])
						this.selectedDay = parseInt(segments[2])
						
						this.clearedDate = false
					}

				}
			},
			clearDate() {
				this.clearedDate = true
				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)
			},
			closeCalendar() {
				this.calendarOpen = false
			},
			goToToday() {
				let d = new Date()
				this.selectedYear = d.getFullYear()
				this.selectedMonth = d.getMonth()+1
				this.selectedDay = d.getDate()

				this.calendarOpen = false

				this.clearedDate = false

				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)
			},
			changeMode() {
				if (this.mode == "months")
					this.mode = "years"
				else if (this.mode == "years")
					this.mode = "days"
				else
					this.mode = "months"

			},
			decrement() {
				if (this.mode == "days") {
					if (this.selectedMonth == 1) {
						this.selectedMonth = 12
						this.selectedYear--
					}
					else
						this.selectedMonth--
				}
				else if (this.mode == "months") 
					this.selectedYear--
				else 
					this.selectedYear -= 12
				
				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)

			},
			increment() {
				if (this.mode == "days") {
					if (this.selectedMonth == 12) {
						this.selectedMonth = 1
						this.selectedYear++
					}
					else
						this.selectedMonth++
				}
				else if (this.mode == "months") 
					this.selectedYear++
				else 
					this.selectedYear += 12
				
				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)

			},
			selectDay(n) {
				this.clearedDate = false
				this.selectedDay = n
				this.calendarOpen = false

				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)
			},
			selectMonth(n) {
				this.clearedDate = false
				this.selectedMonth = n
				this.mode = "days"

				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)
			},
			selectYear(year) {
				this.clearedDate = false
				this.selectedYear = year
				this.mode = "months"

				this.$emit("input",this.selectedFullDate)
				this.$emit("change",this.selectedFullDate)
			},
			startingDayDayofWeek(month,year) {
				if (month == 0) {
					month = new Date().getMonth()
					this.selectedMonth = month
				}
				if (year == 0) {
					year = new Date().getFullYear()
					this.selectedYear = year
				}

				let firstDay = new Date(year,month-1,1)

				return firstDay.getDay()
			},
			endingDayDayofWeek(month,year) {
				if (month == 0) {
					month = new Date().getMonth()
					this.selectedMonth = month
				}
				if (year == 0) {
					year = new Date().getFullYear()
					this.selectedYear = year
				}

				let lastday = new Date(year,month-1,this.numberDaysInMonth(month,year))

				return lastday.getDay()
			},
			startingDay(month, year) {

				/*if (month == 0 && year == 0) {
					year = new Date().getFullYear()
				}*/

				let prevMonth = month-1
				let prevYear = year
				if (month == 1) {
					prevYear = year - 1
					prevMonth = 12
				}

				let numberDaysInMonth = this.numberDaysInMonth(prevMonth,prevYear)

				let firstSunday = numberDaysInMonth - this.startingDayDayofWeek(month,year)

				return firstSunday
			},
			numberDaysInMonth(month, year) {

				month = parseInt(month)

				switch(month) {
					case 1:
						return 31
						break;
					case 2:
						if (year == 2020 ||
							year == 2024 ||
							year == 2028 ||
							year == 2032 ||
							year == 2036)
							return 29
						else
							return 28
						break;
					case 3:
						return 31
						break;
					case 4:
						return 30
						break;
					case 5:
						return 31
						break;
					case 6:
						return 30
						break;
					case 7:
						return 31
						break;
					case 8:
						return 31
						break;
					case 9:
						return 30
						break;
					case 10:
						return 31
						break;
					case 11:
						return 30
						break;
					case 12:
						return 31
						break;
						
				}
			}
		},
		directives: {
			'click-outside': {
				bind: function(el, binding, vNode) {
					// Provided expression must evaluate to a function.
					if (typeof binding.value !== 'function') {
						const compName = vNode.context.name
						let warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`
						if (compName) { warn += `Found in component '${compName}'` }

						console.warn(warn)
					}
					// Define Handler and cache it on the element
					const bubble = binding.modifiers.bubble
					const handler = (e) => {
						if (bubble || (!el.contains(e.target) && el !== e.target)) {
							binding.value(e)
						}
					}
					el.__vueClickOutside__ = handler

						// add Event Listeners
						document.addEventListener('click', handler)
				},

				unbind: function(el, binding) {
					// Remove Event Listeners
					document.removeEventListener('click', el.__vueClickOutside__)
					el.__vueClickOutside__ = null

				}
			}
		},
		created() {

			//set initial date to be today
			let d = new Date()

			this.selectedYear = d.getFullYear()
			this.selectedMonth = d.getMonth()+1
			this.selectedDay = d.getDate()

			//set ititial date only if value is not already bound
			if (this.initialDate && !this.value) {

				if (this.initialDate.toLowerCase() != "today") { //if initial date is set to 'today' then we'll leave it as is, because today is already set. Otherwise set the date that's been passed in



					//split by dashes
					let segments = this.initialDate.split("-")

					this.selectedYear = segments[0]
					this.selectedMonth = segments[1]
					this.selectedDay = segments[2]
				}
			}
			else
				this.clearedDate = true

			this.setValue()

			//this.$emit("input",this.selectedFullDate)
			//this.$emit("change",this.selectedFullDate)
		}
	}
</script>

<style type="text/css" scoped>
	.datepicker-wrapper {
		position: relative;
	}

	.datepicker-input-wrapper {
		position: relative;

	}

	.datepicker-input-wrapper .fa {
		color: #a0a0a0;
		position: absolute;
		right: 10px;
		top: 10px;
	}

	.datepicker-calendar {
		position: absolute;
		top: 42px;
		left: 1px;
		width: 220px;
		box-shadow: 0 0 12px #505050;
		z-index: 11;
		background-color: white;
		padding: 5px;
		border-radius: 3px;
		text-align: center;
	}

	.datepicker-header-controls {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
	}

	.datepicker-calendar:before {
		top: -13px;
		left: 20px;
		border: solid transparent;
		border-bottom-color: white;
		border-width: 7px;
		content: " ";
		position: absolute;
		width: 0;
		height: 0;
		z-index: 10;
	}

	.datepicker-header-controls button {
		font-weight: bold;
		border: none;
		background: white;
		padding: 3px;
		cursor: pointer;
		min-width: 30px;
		border-radius: 2px;
	}

	.datepicker-header-controls button:hover {
		cursor: pointer;
		background-color: #e0e0e0;
	}

	.datepicker-days-of-week-headings {
		font-weight: bold;
	}

	.datepicker-days-of-month div {
		display: inline-block;
		width: 14.28%;
	}

	.datepicker-days-of-month .selected-day {
		background-color: #286090;
		color: white;
	}

	.datepicker-day-button,  .datepicker-today-button {
		padding: 3px;
		border-radius: 2px;
	}

	.datepicker-day-button:hover, .datepicker-today-button:hover {
		cursor: pointer;
		background-color: #e0e0e0;
	}

	.datepicker-other-month-day {
		color: #a0a0a0;

	}

	.datepicker-month-of-year, .datepicker-year-selecter {
		display: inline-block;
		width: 33.3%;
		padding: 3px;
		border-radius: 2px;
	}

	.datepicker-month-of-year:hover, .datepicker-year-selecter:hover {
		cursor: pointer;
		background-color: #e0e0e0;
	}

	.form-inline .form-control {
		width: 130px;
	}
</style>