<template>
  <div class="reservation">
    <div class="row">
      <div class="col-sm-12 col-md-12 ">
        <div class="selected_room">{{roomName}}</div>
      </div>
      </div> 

    <div class="form-group">
      <label>
        1. Booked for
      </label>
      <div class = "row">
        <div  class="col-sm-3 col-md-3">
          <select v-model="userId" class="form-control">
            <option v-for="user in users" :value="user.id" :key="user.id">
              {{user.fullname}}
            </option>
          </select>
        </div>
        <div  class="col-sm-9 col-md-9"></div>
      </div>
    </div>

    <div class="form-group">
      <label>
        2. I would like to book this meeting:
      </label>
      <div class = "row">
        <div  class="col-sm-2 col-md-2">
          <select v-model="month" class="form-control">
            <option v-for="monthEl in monthes" :value="monthEl.id" :key="monthEl.id">
              {{monthEl.name}}
            </option>
          </select>
        </div>
        <div  class="col-sm-1 col-md-1">
          <select v-model="day" class="form-control">
            <option v-for="(dayNum, index) in days" :value="dayNum" :key="index">
              {{dayNum}}
            </option>
          </select>
        </div>
        <div  class="col-sm-2 col-md-2">
          <select v-model="year" class="form-control">
            <option v-for="(yearNum,index) in years" :value="yearNum" :key="index">
              {{yearNum}}
            </option>
          </select>
        </div>
        <div  class="col-sm-7 col-md-7"></div>
      </div>
    </div>

    <div class="form-group">
      <label>
        3. Specify what the time and end  of the meeting 
          (This will be what people see on the calendar.):
      </label>
      <!--if 24 hour time format -->
      <div class="red">{{errTime}}</div>
        <div class = "row" v-if="f24">
          <div  class="col-sm-1 col-md-1">
            <select v-model="hourStart" class="form-control">
              <option v-for="(hourNum, index) in hours24start" :value="hourNum" :key="index">
                {{hourNum}}
              </option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="minuteStart" class="form-control">
              <option  value="0">00</option>
              <option  value="30">30</option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1" style="text-align:center"> - </div>
            <div  class="col-sm-1 col-md-1">
              <select v-model="hourEnd" class="form-control">
                <option v-for="(hourNum, index) in hours24end" :value="hourNum" :key="index">
                  {{hourNum}}
                </option>
              </select>
            </div>
            <div  class="col-sm-1 col-md-1">
              <select v-model="minuteEnd" class="form-control">
                <option  value="0">00</option>
                <option  value="30">30</option>
              </select>
            </div>
            <div  class="col-sm-7 col-md-7"></div>
        </div>
        <!--if 12 hour time format -->
        <div class = "row" v-if="f12">
          <div  class="col-sm-1 col-md-1">
            <select v-model="hourStart" class="form-control">
              <option v-for="hourNum in hours12start" :value="hourNum" :key="hourNum">
                {{hourNum}}
              </option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="minuteStart" class="form-control">
              <option  value="0" >00</option>
              <option  value="30">30</option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="dayPartStart" class="form-control">
              <option  value="am">AM</option>
              <option  value="pm">PM</option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1" style="text-align:center"> - </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="hourEnd" class="form-control">
              <option v-for="hourNum in hours12end" :value="hourNum" :key="hourNum">
                {{hourNum}}
              </option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="minuteEnd" class="form-control">
              <option  value="0">00</option>
              <option  value="30">30</option>
            </select>
          </div>
          <div  class="col-sm-1 col-md-1">
            <select v-model="dayPartEnd" class="form-control">
              <option  value="am">AM</option>
              <option  value="pm">PM</option>
            </select>
          </div>
          <div  class="col-sm-5 col-md-5"></div>
        </div>
      </div>

      <div class="form-group">
      <label>
        4.Enter the specifics for the meeting.(This will be what people
          see when they click on an event link.)
      </label>
      <div class="row">
            <div  class="col-sm-5 col-md-5">
              <textarea class="form-control" v-model="description" rows="5" 
                        @keydown="descriptionCheck" 
                        title="permissible symbols: letters of the Latin alphabet, numbers, and also !?()=: . , ; ' № % $ -">
              </textarea>
              <p class="err_small">{{errDesc}}</p>
            </div>
            <div  class="col-sm-7 col-md-7"></div>
          </div>
      </div> 

      <div class="form-group">
        <label>
          5. Is this going to be a recurring event?
        </label>
        <div class="radio">
          <label>
            <input type="radio" name="recur" id="recur1" value="1" v-model="isRecur" >yes
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="recur" id="recur2" value="0" v-model="isRecur" >no
          </label>
        </div>
      </div>   

      <div class="form-group" v-if="isRecuriveEvent">
        <label>
          6. If it is recurring, specify weekly, bi-weekly, or monthly.
        </label>
        <div class="radio">
          <label>
            <input type="radio" name="recur_period" id="recurp1" value="w" v-model="recurPeriod" >weekly
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="recur_period" id="recurp2" value="b" v-model="recurPeriod" >bi-weekly
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="recur_period" id="recurp3" value="m" v-model="recurPeriod" >monthly
          </label>
        </div>
         
        <label> 
          If weekly or bi-weekly, specify the number of weeks for it to keep recurring. 
          If monthly, specify the number of months. 
          (If you choose "bi-weekly" and put in an odd number of weeks,
          the computer will round down.)
        </label>  
        <div class="row">
          <div  class="col-sm-1 col-md-1"> 
            <input class="form-control num_only" v-model="duration"
                  @keydown="durationCheck" 
                  oninput="if(this.value>10) this.value = this.value%10  "/>
          </div>
          <div  class="col-sm-2 col-md-2"> 
            <label v-if="recurPeriod == 'w'">duration(max 4 weeks)</label>
            <label v-if="recurPeriod == 'b'">duration(max 2 weeks)</label>
            <label v-if="recurPeriod == 'm'">duration(max 1 month)</label>
          </div>
          <div  class="col-sm-9 col-md-9"> </div>
        </div>
      </div>   
      <!-- result block-->
      <div class="red">{{errAdd}}</div>
      <div>
        <div>{{resAdd}}</div>
        <ul>
          <li v-for="(ev, index) in addedEvents" :key="index">
            {{ev.event.start}} -  {{ev.event.end}}  - 
            <span v-if="ev.suc==0" class="red">not</span> 
            added
          </li>
        </ul>
      </div>

      <button class="btn btn-auth" @click="add_event">Submit</button> 
      <button class="btn btn-auth" @click="cancel" type="cancel">Cancel</button>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'reservation',
  props: ['hourFormat'],
  data () {
    return {
      roomId: 0,
      roomName: "",
      userId: "",
      monthesTempl : [
        {"id":0, "name":"January"}, 
        {"id":1, "name":"February"},
        {"id":2, "name":"March"}, 
        {"id":3, "name":"April"}, 
        {"id":4, "name":"May"},
        {"id":5, "name":"June"}, 
        {"id":6, "name":"July"}, 
        {"id":7, "name":"August"}, 
        {"id":8, "name":"September"},
        {"id":9, "name":"October"},
        {"id":10, "name":"November"},
        {"id":11, "name":"December"}],
      users: [],
      year: 0,
      month: 0,
      day: 0,
      hourStart: 0,
      minuteStart: 0,
      hourEnd: 0,
      minuteEnd: 0,
      dayPartStart: 'am',
      dayPartEnd: 'am',
      description: '',
      isRecur: 1,
      recurPeriod: 'w',
      addedEvents: [],
      user: {},
      resAdd: '',
      isAdmin: false,
      duration: 1,
      errDesc: '',
      errTime: '',
      errAdd: '',
    }
  },
  created(){
    var self = this;
    if(localStorage['hashLog']) {
      axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
        .then(function (response) {
          var userId = parseInt(response.data.data.id); 
          if(userId > 0){
            self.user = {'id': response.data.data.id, 'fullname' :  response.data.data.fullname};
            self.userId =  self.user.id;
            if(response.data.data.role == 'admin'){
              self.isAdmin = true;
            }
              
            if(self.isAdmin) {
              self.getUsers();
            }else{
              self.users = [self.user];
            }

            self.roomId =  self.$route.params.roomId
            self.getRoomName();
            self.getCurParams();
          }else{
            self.$emit('logout');
          }
        }).catch(function (error) {
          self.$emit('logout');
        });
    }else{
      this.$emit('logout');
    }
  },
  methods:{
    /*
    the function of forming the initial output settings by the current date
    */
    getCurParams() {
      var now = new Date(); 
      this.year = now.getFullYear();
      var len = this.monthesTempl.length;
      for(var i = 0; i < len; i++) {
        if(this.monthesTempl[i].id == now.getMonth()) {
          this.month = this.monthesTempl[i].id;
          break;
        }
      }
      this.day = now.getDate();
      var monuteNow = now.getMinutes();
      if(monuteNow >10) {
        this.hourStart = now.getHours() + 1;
        this.minuteStart = 0;
        this.hourEnd = this.hourStart;
        this.minuteEnd = 30;
      }else {
        this.hourStart = now.getHours();
        this.minuteStart = 30;
        this.hourEnd = this.hourStart + 1 ;
        this.minuteEnd = 0;
      }
    },

    /*
    function to get the name of the room to be reserved by its id
    */
    getRoomName() {
      var self = this;
      axios.get(serverUrl + 'BOOKER/client/api/room/' + this.roomId, this.config)
        .then(function (response) {
          self.roomName = response.data.data.name;
        })
    },

    /*
    return initial settings function
    */
    cancel() {
      this.description = this.errAdd = this.resAdd = '';
      this.isRecur = 1;
      this.recurPeriod = 'w';
      this.duration = 1;
      this.addedEvents = [];
      this.getCurParams();
    },

    /*
    function of getting information about users wishing to reserve a room
    */
    getUsers(){
      var self = this;
      axios.get(serverUrl + 'BOOKER/client/api/user/', this.config)
        .then(function (response) {
          self.users = response.data.data
      })
      .catch(function (error) {
        //console.log(error);
      });
    },

    /*
    the function of tracking and blocking the incorrect entry
    of the number of repetitions when booking
     */
    durationCheck: function(event){
      if(this.recurPeriod == 'w' && (event.keyCode < 49 || event.keyCode > 52) 
                                  && (event.keyCode < 97 || event.keyCode > 100) )
      {
        event.preventDefault();
      }else if(this.recurPeriod == 'b' && (event.keyCode < 49 || event.keyCode > 50) 
                                        && (event.keyCode < 97 || event.keyCode > 98))
      {
        event.preventDefault();
      }else if(this.recurPeriod == 'm' && (event.keyCode != 49))
      {
        event.preventDefault();
      }
    },

    /*
    validation description
    @return <Bool> flag - 
      return true - if description is valid and false otherwise
    */
    descriptionCheck: function() {
      var re = /^[!?\(\)=:.,;'"№%$\-\w\s]+$/;
      if (re.test(this.description) !== true) {
        this.errDesc = 'text contains invalid characters';
        return false;
      }else {
        this.errDesc = '';
        return true;
      }     
    },

    /*
    add event function
     */
    add_event() {
      if(!this.descriptionCheck()) {
        this.errAdd = 'description contains invalid characters';
        return;
      }

      this.err_time = ""
      this.errAdd = this.res_add = '';
      var unixDayStart = null;
      var unixDayEnd = null;
   
      if(this.hourFormat == 'h12') {
        if(this.dayPartStart == "pm" && this.hourStart < 12) {
          this.hourStart += 12;
        }
        if(this.dayPartEnd == "pm" && this.hourEnd < 12) {
          this.hourEnd += 12;
        }
      }
      var  unixTimeStart = this.getUnixTimestamp(this.year, this.month, this.day, this.hourStart, this.minuteStart);
      var  unixTimeEnd = this.getUnixTimestamp(this.year, this.month, this.day, this.hourEnd, this.minuteEnd);
      var unixDayEvent = this.getUnixTimestamp(this.year, this.month, this.day, 0, 0);

      if(unixTimeStart >= unixTimeEnd){
        this.errTime = 'Uncorrect time. Time start must be less then time end and min time event = 30 minutes';
        var self = this;
        setTimeout(function () {
          self.errTime = '';
        },2500);
      }else{
        var isRepeateDb = false, durationDb = null, recurPeriodDb = null;
        if(this.isRecur == 1) {
          isRepeateDb  = true;
          recurPeriodDb = this.recurPeriod;
          durationDb = this.duration;
        }

        var data = new FormData();
        data.append("user_id", this.userId);
        data.append("room_id", this.roomId);
        data.append("description", this.description);
        data.append("start_event", unixTimeStart);
        data.append("end_event", unixTimeEnd);
        data.append("day_event", unixDayEvent); 
        data.append("is_repeate", isRepeateDb);  
        data.append("recur_period", recurPeriodDb);  
        data.append("duration", durationDb); 

        var self = this;
        if(localStorage['hashLog']) {
          axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
            .then(function (response) {
              var userId = parseInt(response.data.data.id) 
              if(userId > 0) {
                axios.post(serverUrl + 'BOOKER/client/api/event/', data, self.config)
                .then(function (response) {
                  if(response.data.err && response.data.err == true) {
                    self.errAdd = response.data.data;
                    setTimeout(function () {
                      self.errAdd = '';
                    },2500);
                  }else {
                    var count = parseInt(response.data.data.count);
                    if(count > 0) {
                      self.addedEvents = response.data.data.events;
                      self.resAdd = count + ' event(s) was(were) successfully added';
                      if(self.isRecur == true && count < ++self.duration_db) {
                        self.resAdd += 'If the claimed armor is less than the added events - perhaps the room at this time is booked. Check on the calendar.';
                      }
                    }else {
                      self.errAdd = 'sorry, there was a server error.Try again later';
                      setTimeout(function () {
                        self.errAdd = '';
                      },2500);
                    }
                  }
                })
                .catch(function (error) {
                  self.errAdd = 'sorry, there was a server error.Try again later';
                });
              }else {
                self.$emit('logout');
              }
            }).catch(function (error) {
              self.$emit('logout');
            });
          }else {
            this.$emit('logout');
          }
      }
    },

    getUnixTimestamp : function(year, month, date, hours, minutes) {
      return Math.floor(new Date(year, month, date, hours, minutes, 0, 0).getTime() / 1000);
    },

  },
  computed:{
    /*
    list-years generation function
    @return <Array> years
    */
    years: function(){
      var now = new Date(); 
      var yearNow = now.getFullYear();
      return [yearNow, yearNow + 1];
    },

    /*
    list-monthes generation function
    @return <Array> monthes
    */
    monthes: function() {
      var now = new Date();
      var yearNow = now.getFullYear();
      var monthNow = now.getMonth();
      if(this.year == yearNow) {
        var temp = [];
        this.monthesTempl.forEach(function(month) {
          if(month.id >= monthNow);
            temp.push(month);
        }, this);
        return temp;
      }
      return this.monthesTempl;
    },

    /*
    list-date generation function
    @return <Array> dates
    */
    days: function() {
      var daysInCurrentMonth = 32 - new Date(this.year, this.month, 32).getDate(); 
      var now = new Date(); 
      var m = now.getMonth();
      var y = now.getFullYear();
      var d = now.getDate();
      var daysTemp = [];
      for(var i = 1; i <= daysInCurrentMonth; i++) {
        if(m == this.month  && this.year == y && i < d){
          continue;
        }
        daysTemp.push(i);
      }
      return daysTemp;
    },

    /*
    list-hours start event in 12-hours format generation function
    @return <Array> hours
    */
    hours12end: function() {
      if(this.dayPartEnd == 'am') {
          return hoursAm;
        }else {
          return hoursPm;
        }
    },

    /*
    list-hours end event in 12-hours format generation function
    @return <Array> hours
    */
    hours12start: function() {
      if(this.dayPartStart == 'am') {
        return hoursAm;
      }else {
        var temp = hoursPm.slice();
        temp.pop();
        return temp;
      }
    },

     /*
    list-hours start event in 24-hours format generation function
    @return <Array> hours
    */
    hours24start: function() {
      var temp = hours24.slice();
      temp.pop();
      return temp;
    },

    /*
    list-hours end event in 24-hours format generation function
    @return <Array> hours
    */
    hours24end: function(){
      return hours24;
    },

    /*
    time format tracking function
    @return <Bool> flag
     return true if 12 hours fotmat changed
    */
    f12: function() {
      if(this.hourFormat == 'h12') {
        return true;
      }
      return false;
    },

    /*
    time format tracking function
    @return <Bool> flag
     return true if 24 hours fotmat changed
    */
    f24: function() {
      if(this.hourFormat == 'h24') {
        return true;
      }
      return false;
    },

    /*
    event recursiveness tracking function
    @return <Bool> flag
     return true if event is recursive
    */
    isRecuriveEvent: function() {
      if(this.isRecur == '1') {
        return true;
      }
      return false;
    },
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.reservation{
  margin-top: -70px;
}

.selected_room{
  margin-bottom: 20px;
}

.red{
  color: red;
}
</style>
