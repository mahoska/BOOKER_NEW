<template>
  <div class="calendar">
    <!--nav from rooms-->
    <div class="row">
      <div class="col-sm-12 col-md-12">
        <ul class="nav nav-tabs">
          <li role="presentation" v-for="room in rooms" :key="room.id">
            <a href="#" @click="getInfoRoom(room)">{{room.name}}</a>
          </li>
        </ul>
      </div>
    </div>
    <!--end nav from rooms-->
    <div class="row">
      <div class="col-sm-12 col-md-12 ">
        <div class="selected_room">{{selectRoomName}}</div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12 col-md-12">
        <!--calendar-->
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <li>
              <a href="#" aria-label="Previous" @click="previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
            <li><a href="#">{{month}} {{currentYear}}</a></li>
            <li>
              <a href="#" aria-label="Next" @click="next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-10 col-md-10">
        <table class="table table-bordered">
          <thead>
            <tr>
              <td v-for="(wday, index) in weekDay" :key="index" class="week-day">{{wday}}</td>
            </tr>
          </thead>
          <tbody>
              <tr v-for="(i,index) in dates" :key="index">
                <td v-for="(day,index) in i" :key="index" >
                  <day-section :day='day' :hformat='hourFormat' :room='selectRoomName' @updateCalendar="updateCalendar"></day-section>
                  
                </td>
              </tr v-if="i.end">
          </tbody>
        </table>
      </div>
    <!--end calendar-->
    <!--rightbar-->
    <div class="col-sm-2 col-md-2">
      <div class="right-bar">
        <button class="btn btn-auth" @click="changeStartWeek">Change start week</button>
        <button class="btn book"><router-link  :to="'/reservation/'+ selectRoomId">Book it!</router-link></button>
      </div>
    </div>
    <!-- end rightbar-->
  </div>

  </div>
</template>

<script>
import axios from 'axios'
import DaySection from './DaySection.vue'
export default {
  name: 'calendar',
  props: ['hourFormat'],
  data () {
    return {
      currentYear: 0,
      currentMonth: 0,
      monthes : ["January", "February",
                 "March", "April", 
                 "May", "June", 
                 "July", "August", 
                 "September","October",
                 "November","December"],
      weekDayM : ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
      weekDayS : ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
      weekDay: [],
      month: '',
      indexes: [],
      dayOfWeek: 0,
      rooms: [],
      selectRoomId: 0,
      selectRoomName: '',
      events: [],
      dates: [],
      startWeek:false
    }    
  },
  components: {
		'day-section': DaySection
  },
  created(){
      var now = new Date();
      this.currentYear = now.getFullYear(); 
      this.currentMonth = now.getMonth();
      var d = now.getDate();
      
      this.getRooms();
      this.getInfoRoom({'id': this.selectRoomId, 'name': this.selectRoomName}) ;

      this.weekDay = this.weekDayM;
  },
  methods:{
    updateCalendar: function() {
       this.getInfoRoom({'id': this.selectRoomId, 'name': this.selectRoomName});
    },

    /*
      function of obtaining information about reserved rooms
    */
    getRooms: function() {
      var self = this;
      axios.get(serverUrl +'BOOKER/client/api/room/', this.config)
        .then(function (response) {
          self.rooms = response.data.data;

          //initializing data for primary output
          self.selectRoomId = self.rooms[0].id;
          self.selectRoomName = self.rooms[0].name;
          self.getInfoRoom(self.rooms[0]);     
      })
      .catch(function (error) {
        //console.log(error);
      });
    },

    /*
      function of getting information about a particular room
      @param <Object> room  
        -  contains properties id, name booked room
    */
    getInfoRoom: function(room) {
      this.selectRoomId = room.id;
      this.selectRoomName = room.name;
      this.getDataForMonth();
    },

    /*
      function of obtaining information about the reservation 
      of the selected room for the previous month
    */
    previous: function() {
      this.currentMonth--;
      if(this.currentMonth == 0) {
        this.currentMonth = 11;
        this.currentYear--;
      }
      this.getDataForMonth();
    },

    /*
      function of obtaining information about the reservation 
      of the selected room for the next month
    */
    next: function() {
      this.currentMonth++;
      if(this.currentMonth == 12) {
        this.currentMonth = 0;
        this.currentYear++;
      }
      this.getDataForMonth();
    },

    /*
      function to obtain information about 
      the reservation of the selected room for the selected month
    */
    getDataForMonth: function() {
      var self = this;
      var curMonth = this.getUnixTimestamp(this.currentYear, this.currentMonth, 1, 0, 0);
      var daysInCurrentMonth = 32- new Date(this.currentYear, this.currentMonth , 32).getDate(); 

      axios.get(serverUrl+'BOOKER/client/api/event/'+ this.selectRoomId + '/' + curMonth + '/' + daysInCurrentMonth, this.config)
        .then(function (response) {
          self.events = response.data.data;
          self.getDates();
        })
        .catch(function (error) {
          //console.log(error);
        });
    },

    /*
    date translation function in timestamp format
    @param <Integer> year 
    @param <Integer> month 
    @param <Integer> date 
    @param <Integer> hours 
    @param <Integer> minutes
    @return <Integer> seconds -  Return the number of seconds since 1970/01/01
    */
    getUnixTimestamp: function(year, month, date, hours, minutes) {
      return Math.floor(new Date(year, month, date, hours, minutes, 0, 0).getTime() / 1000);
    },

    /*
    function to convert the date from the timestamp format to the Date format
    @param <Integer> timestamp - the number of seconds since 1970/01/01
    @return <Object> date - 
      returns an instance of the Date object representing the time point
     */
    timestampToDate: function(timestamp) {
      return  new Date(timestamp * 1000); 
    },

    /*
    function that generates data for building a calendar
    */
    getDates: function() {
      var tempDates = [];

      // Determine the current date
      var data = new Date(this.currentYear,this.currentMonth,1);
      
      // Set the variable to the first day of the current month
      data.setDate(1); 
      
      // Determine the day of the week
      this.dayOfWeek = data.getDay();    
      if (this.dayOfWeek == 0) {   
        this.dayOfWeek = 7;
      }

      // Recognize the month, year
      this.currentMonth = data.getMonth();   
      this.currentYear = data.getFullYear(); 

      // Determine the number of days in the current month
      var daysInCurrentMonth = 32- new Date(this.currentYear, this.currentMonth, 32).getDate(); 

      var prevMonth = this.currentMonth - 1; 
      if (this.currentMonth == 0) {
        prevMonth = 11;
      }
      
      var prevYear = this.currentYear; 
      if (this.currentMonth == 0) {
        prevYear = this.currentYear - 1;
      }

      var daysInPrevMonth = 32- new Date(prevYear, prevMonth , 32).getDate(); 

      // Recognize the current number
      var today = data.getDate(); 
    
      this.month = this.monthes[this.currentMonth];

      var daysNum = [];
      var id = 0, pushfl = 0, count = 0;
      for(var i = 2 - this.dayOfWeek; i <= 43 - this.dayOfWeek; i++) {
        if ((i - 1 + this.dayOfWeek) % 7 == 1) {
          daysNum = [];
        }
        id = 0;
        if(i <= 0) {
          id =  -(daysInPrevMonth + i);
        }
        else if (i > daysInCurrentMonth) {
          id =  -(i - daysInCurrentMonth); 
        }
        else {
          id = i;
        }

        if(this.startWeek) {
          --id;
        }

        if(id>0) { 
          daysNum.push({'day': id, 'cur': true, 'day_event': this.events[id]});
        }
        else {
          daysNum.push({'day':'', 'cur': false, 'day_event': []});
          pushfl++;
        }
        count++;
      

        if ((i - 1 + this.dayOfWeek) % 7 == 0 ) {
          if (pushfl != 7) {
            tempDates.push(daysNum);  
          }
          count = 0;
          pushfl = 0;  
        }
      }

      this.dates =  tempDates;
    },


  /*
  the function of the rebuild calendar, 
  depending on the selected starting day of the week (monday or Sunday)
  */
  changeStartWeek: function() {  
    this.startWeek = !this.startWeek;
    if(this.startWeek) {
      this.weekDay = this.weekDayS;
    }
    else {
      this.weekDay = this.weekDayM;
    }
    this.getDataForMonth();   
  }
}

}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.calendar{
  margin-top: -70px;
}

.week-day{
  color: red;
   font-size:11pt; 
   background-color:#E6FDFF;
}

.right-bar{
  text-align: center;
}

.book{
  margin-top: 50px;
}
</style>
