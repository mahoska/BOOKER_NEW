<template>
  <div class="event">
    <div v-if="isNotActiveUser">
      This operation is currently unavailable. 
      Your authorization period may be expired on the site.
    </div>
    <div class="err_small">{{errDel}}</div>
    <div>{{resDel}}</div>
    <div v-if="!disactive">
     <div class="row">
      <div class="col-sm-12 col-md-12 ">
        <div class="selected_room">{{room}} BOOKED DETAILS</div>
      </div>
    </div>

    When: 
    <!--if 24 hour time format -->
    <div class="err_small ">{{errTime}}</div>
    <div class = "row" v-if="f24 == true">
    <div  class="col-sm-2 col-md-2">
      <select v-model="hourStart" class="form-control select-event-time" >
        <option v-for="(hourNum, index) in hours24start" :value="hourNum" :key="index">
          {{hourNum}}
        </option>
      </select>
    </div>
    <div  class="col-sm-2 col-md-2">
      <select v-model="minuteStart" class="form-control select-event-time" >
        <option  value="0">00</option>
        <option  value="30">30</option>
      </select>
    </div>
    <div  class="col-sm-1 col-md-1" style="text-align:center"> - </div>
      <div  class="col-sm-2 col-md-2">
        <select v-model="hourEnd" class="form-control select-event-time">
          <option v-for="(hourNum, index) in hours24end" :value="hourNum" :key="index">
            {{hourNum}}
          </option>
        </select>
      </div>
      <div  class="col-sm-2 col-md-2">
        <select v-model="minuteEnd" class="form-control select-event-time" >
          <option  value="0">00</option>
          <option  value="30">30</option>
        </select>
      </div>
      <div  class="col-sm-3 col-md-3"></div>
    </div>

    <!--if 12 hour time format -->
    <div class = "row" v-if="f12 == true">
      <div  class="col-sm-2 col-md-2">
        <select v-model="hourStart" class="form-control select-event-time" >
          <option v-for="hourNum in hours12start" :value="hourNum" :key="hourNum">
            {{hourNum}}
          </option>
        </select>
      </div>
      <div  class="col-sm-2 col-md-2">
        <select v-model="minuteStart" class="form-control select-event-time">
          <option  value="0" >00</option>
          <option  value="30">30</option>
        </select>
      </div>
      <div class="col-sm-1 col-md-1">
        <select v-model="dayPartStart" class="form-control select-event-time" >
          <option  value="am">AM</option>
          <option  value="pm">PM</option>
        </select>
      </div>
      <div class="col-sm-1 col-md-1" style="text-align:center"> - </div>
      <div class="col-sm-2 col-md-2">
        <select v-model="hourEnd" class="form-control select-event-time">
          <option v-for="hourNum in hours12end" :value="hourNum" :key="hourNum">
            {{hourNum}}
          </option>
        </select>
      </div>
      <div  class="col-sm-2 col-md-2">
        <select v-model="minuteEnd" class="form-control select-event-time">
          <option  value="0">00</option>
          <option  value="30">30</option>
        </select>
      </div>
      <div class="col-sm-1 col-md-1">
        <select v-model="dayPartEnd" class="form-control select-event-time">
          <option  value="am">AM</option>
          <option  value="pm">PM</option>
        </select>
      </div>
      <div class="col-sm-1 col-md-1"></div>
    </div>

    Notes:
    <div class="row">
      <div class="col-sm-5 col-md-5">
        <textarea class="form-control" v-model="event.description" rows="5" 
                  @keydown="descriptionCheck" 
                  title="permissible symbols: letters of the Latin alphabet, numbers, and also !?()=: . , ; ' № % $ -">
        </textarea>
        <p class="err_small">{{errDesc}}</p>
      </div>
      <div  class="col-sm-7 col-md-7"></div>
    </div> 

    Who:
      <div class = "row">
      <div  class="col-sm-3 col-md-3">
        <select v-model="event.user_id" class="form-control">
          <option v-for="user in users" :value="user.id" :key="user.id">
            {{user.fullname}}
          </option>
        </select>
      </div>
      <div  class="col-sm-9 col-md-9"></div>
    </div>
      
    <div class="create">
      Submitted: {{event.data_create}}
    </div>

    <div class="checkbox" v-if="showIsRecurse">
      <label>
        <input type="checkbox"  v-model="isRecurse" v-if="isAdmin">Apply to occurrences?
      </label>
    </div>
    <!--result block-->
    <div>
      <div>{{resUpd}}</div>
      <ul>
        <li v-for="(ev, index) in updatedEvents" :key="index">
          {{ev.event.start}} -  {{ev.event.end}}  - <span v-if="!ev.suc" class="red">not</span> updated
        </li>
      </ul>
    </div>
    <!--end result block-->
    <button class="btn btn-auth" @click="updateEvent" v-if="isAdmin">Update</button> 
    <button class="btn btn-auth" @click="deleteEvent" v-if="isAdmin">Delete</button>  
  </div>
</div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'event',
  data () {
    return {
      eventId: 0,
      hourFormat: 'h12',
      showInfo: true,
      user: {},
      users: [],
      event: {},
      hourStart: 0,
      minuteStart: 0,
      hourEnd: 0,
      minuteEnd: 0,
      dayPartStart: 'am',
      dayPartEnd: 'am',
      isRecurse: false,
      showIsRecurse: false,
      disactive: false,
      isNotActiveUser: false,
      updatedEvents: [],
      role: 'user',
      isAdmin: true,
      year: 0,
      month: 0,
      day: 0,
      errTime: '',
      errDesc: '',
      errDel: '',
      resDel: '',
      resUpd: '',
    }
  },
  created() {
    this.eventId =  this.$route.params.eventId;
    this.hourFormat =  this.$route.params.hourFormat;
    this.room =  this.$route.params.room;
 
    var self = this;
    //checking the validity time of the user's authorization
    if(localStorage['hashLog']) {
      axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
        .then(function (response) {
          var userId = parseInt(response.data.data.id); 
          if(userId > 0) {
            self.user = {'id': userId, 'fullname' :  response.data.data.fullname};
            //self.event = response.data.data;
            self.role = response.data.data.role;
            if(self.role == 'admin') {
              self.getUsers();
            }
            //get information about event
            axios.get(serverUrl + 'BOOKER/client/api/event/' + self.eventId, self.config)
            .then(function (response) {
              self.event  = response.data.data;
              var user = {'id': self.event.user_id, 'fullname': self.event.fullname};
              if(self.role == 'user') {
                self.users.push(user);
                if(user.id != self.user.id) {
                  self.isAdmin = false;
                }
              }

              var dateStart = new Date(self.event.start_event * 1000);
              self.year = dateStart.getFullYear();
              self.month = dateStart.getMonth();
              self.day = dateStart.getDate();

              document.title = self.day + '.' + self.month + '.' + self.year + '.';

              self.hourStart = dateStart.getHours();
              self.minuteStart = dateStart.getMinutes();
                 
              var dateEnd = new Date(self.event.end_event * 1000);
              self.hourEnd = dateEnd.getHours();
              self.minuteEnd = dateEnd.getMinutes();

              if(self.hourFormat == 'h12') {
                if(self.hourStart > 12) {
                  self.hourStart -= 12;
                  self.dayPartStart = 'pm';
                }
                else if(self.hourStart == 12){
                  self.dayPartStart = 'pm';
                }
                if(self.hourEnd > 12) {
                  self.hourEnd -= 12;
                  self.dayPartEnd = 'pm';
                }
                else if(self.hourEnd == 12) {
                  self.dayPartEnd = 'pm';
                }
              }

              if(parseInt(self.event.parent_event_id) || parseInt(self.event.is_repeat) > 0) {
                self.showIsRecurse = true;
              }
            }).catch(function (error){
                //console.log(error)
            });
          }else {
            self.disactive = true;
            self.isNotActiveUser = true;
          }
        }).catch(function (error) {
          self.disactive = true;
          self.isNotActiveUser = true;
        });
    }else {
      this.disactive = true;
      this.isNotActiveUser = true;
    }
  },
  methods: {
    /*
    event update function
    */  
    updateEvent() { 
      this.errDel = '';
      var self = this;
      if(this.hourFormat == 'h12') {
        if(this.dayPartStart == 'pm' && this.hourStart < 12) {
            this.hourStart += 12;
        }
        if(this.dayPartEnd == 'pm' && this.hourEnd < 12) {
            this.hourEnd += 12;
        }
      }
      var  date = new Date(this.event.start_event * 1000);
      var  unixTimeStart = this.getUnixTimestamp(this.year, this.month, this.day,
                                                  this.hourStart, this.minuteStart);
      var  unixTimeEnd = this.getUnixTimestamp(this.year, this.month, this.day, 
                                                this.hourEnd, this.minuteEnd);
      var  unixDayEvent = this.getUnixTimestamp(this.year, this.month, this.day, 0, 0);
      var unixNow =  Math.floor(new Date().getTime() / 1000);

      if(unixNow >= unixTimeStart) {
        this.errTime = 'This event has already expired and is not subject to change';
        setTimeout(function () {
          self.errTime = '';
        },2500);
        return;
      }else if(unixTimeStart >= unixTimeEnd) {
        this.errTime = 'Uncorrect time. Time start must be less then time end and min time event = 30 minutes';
        setTimeout(function () {
          self.errTime = '';
        },2500);
        return;
      }else if(!this.descriptionCheck()) {
        this.errDel = 'description contains invalid characters';
        return;
      }else {
        //check that user is active
        if(localStorage['hashLog']) {
          axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
            .then(function (response) {
              var userId = parseInt(response.data.data.id); 
              if(userId > 0) {
                var recur = (self.isRecurse) ? 1 : 0;
                axios.put(serverUrl + 'BOOKER/client/api/event/', 
                {
                  id: self.eventId,
                  description: self.event.description,
                  room_id: self.event.room_id,
                  user_id: self.event.user_id,
                  start_hour: self.hourStart,
                  end_hour: self.hourEnd,
                  start_minute: self.minuteStart,
                  end_minute: self.minuteEnd,
                  unix_start: unixTimeStart,
                  unix_end: unixTimeEnd,
                  unix_day: unixDayEvent,
                  is_recurs: recur
                }, self.config)
                .then(function (response) {
                  if(response.data.err && response.data.err == true) {
                    self.errDel = response.data['data'];
                    setTimeout(function () {
                      self.errDel = '';
                    },3000);
                  }else {  
                    var count = parseInt(response.data.data.count);          
                    if(count > 0) {
                      self.updatedEvents = response.data.data.events;
                      self.resUpd = count + ' event(s) was(were) successfully updated';
                    }else {
                      self.errAdd = 'sorry, there was a server error.Try again later';
                      setTimeout(function () {
                        self.errAdd = '';
                      },2500);
                    }
                  }
                })
                .catch(function (error) {
                  self.errDel = 'somthing wrong, no update was made. ' +
                                'Perhaps you did not provide the data for the update';
                  setTimeout(function () {
                    self.errDel = '';
                  },2500);
                });
              }else {
                self.disactive = true;
                self.isNotActiveUser = true;
              }
            }).catch(function (error) {
              self.disactive = true;
              self.isNotActiveUser = true;
            });
        }else {
          this.disactive = true;
          this.isNotActiveUser = true;
        }
      }
    },

    /*
    event deletion function
    */
    deleteEvent() {
      if (confirm('Are you sure you want to delete this user?')) {
        var self = this;
        var  date = new Date(this.event.start_event * 1000);
        var  unixTimeStart = this.getUnixTimestamp(this.year, this.month, this.day, this.hourStart, this.minuteStart);
        var unixNow =  Math.floor(new Date().getTime() / 1000); 

        if(unixNow >= unixTimeStart) {
          this.errTime = 'This event has already expired and is not subject to change';
          setTimeout(function () {
            self.errTime = '';
          },2500);
          return;
        }
        
        //check that user is active
        if(localStorage['hashLog']) {
          axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
            .then(function (response) {
              var userId = parseInt(response.data.data.id); 
              if(userId > 0) {
                var recur = (self.isRecurse) ? 1 : 0;
                axios.delete(serverUrl + 'BOOKER/client/api/event/' + self.eventId +'/' + recur, self.config)
                  .then(function (response) {
                    if(response.data.err && response.data.err == true) {
                      self.errDel = response.data['data'];
                      setTimeout(function () {
                        self.errDel = '';
                      },2500);
                    }else{  
                      var count = parseInt(response.data.data)         
                      if(count > 0) {
                        self.resDel = count + ' record(s) was(were) deleted.';
                        self.disactive = true;
                        setTimeout(function () {
                          self.resDel = '';
                        },2500); 
                      }else{
                        self.errDel = "Record(s) doesn't deleted";
                        setTimeout(function () {
                          self.errDel = '';
                        },2500); 
                      }
                    }
                  }).catch(function (error) {
                      self.errDel = "Record doesn't deleted";
                      setTimeout(function () {
                        self.errDel = '';
                      },2500); 
                    });
              }else{
                self.disactive = true;
              }
            }).catch(function (error) {
              self.disactive = true;
            });
        }else{
          this.disactive = true;
        }
      }
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
    getUnixTimestamp : function(year, month, date, hours, minutes) {
      //new Date(year, month, date, hours, minutes, seconds, ms)
      //get time  in second as in php (/1000)
      return Math.floor(new Date(year, month, date, hours, minutes, 0, 0).getTime() / 1000);
    },

    /*
    function of getting information about users wishing to reserve a room
    */
    getUsers() {
      var self = this
      axios.get(serverUrl+'BOOKER/client/api/user/', this.config)
        .then(function (response) {
          self.users = response.data.data
      }).catch(function (error) {
        //console.log(error);
      });
    },

    descriptionCheck: function() {
      var re = /^[!?\(\)=:.,;'"№%$\-\w\s]+$/;
      if (re.test(this.event.description) !== true) {
        this.errDesc= 'text contains invalid characters';
        return false;
      }else {
        this.errDesc = '';
        return true;
      }     
    },
  }, 

  computed: {
    /*
      list-hours end event in 12-hours format generation function
      @return <Array> hours
    */
    hours12end: function(){
      if(this.dayPartEnd == 'am') {
            return hoursAm;
          }else {
            return hoursPm;
          }
      },

      /*
      list-hours start event in 12-hours format generation function
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
      hours24end: function() {
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
    }
  }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.event {
  position: relative;
  z-index: 1000;
  font-size: 14px;
  margin-top: -100px;
}

.create {
  margin: 15px 0;
}

.select-event-time {
  width: 100px;
}

</style>