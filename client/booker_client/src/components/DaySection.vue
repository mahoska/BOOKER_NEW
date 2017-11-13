<template>
  <div class="daysection">
    <div v-if="day.cur" class="section-cur">
      <div class="date">{{day.day}}</div>
      <div v-for="(time_event, index) in day.day_event.day_events" :key="index"  class="tbl_time">
        <div class="time_event">
          <a href="#" @click="getInfoEvent(time_event.id)">
            <span v-if="hformat == 'h24'">{{time_event.start_event}} - {{time_event.end_event}}</span>
            <span v-if="hformat == 'h12'">{{time_event.start_event_12}} - {{time_event.end_event_12}}</span>
          </a>
        </div>
      </div> 
    </div>
    <div v-if="day.cur==false" class="section-not-cur">
       <div class="date">{{day.day}}</div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'daysection',
  props: ['hformat', 'day', 'room'],
  data () {
    return {
    }
  },
  methods:{
    /*
    function that creates a window for viewing and editing the selected event
    @param <Integer> eventId  
     */
   getInfoEvent(eventId){
     var w = 1100, h = 620, x = screen.availWidth/2-w/2, y = screen.availHeight/2-h/2; 
     var new_win = window.open (winUrl + '/#/event/'+ eventId + '/' + this.hformat + '/' + this.room,
                   "_blank", "width=" + w + ", height=" + h + ", left=" + x + ", top=" + y + ", resizable=yes" );
     var self = this;           
     new_win.onbeforeunload  = function(){
       self.$emit('updateCalendar');
     }
   }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.section-not-cur, .section-cur{
  max-height: 135px;
  min-height: 135px;
  width: 120px;
  padding: 2px;
  text-align: left;
  overflow: auto;
}

.section-not-cur{
   background: #72B9DF;
}

.date{
  position: relative;
  right: 0px;
  top: -5px;
  text-align: right;
} 

.time_event, .time_event_not_cur{
  font-size: 12px;
  padding-left: 0;
}

.tbl_time{
  position: relative;
  top:-5px;
  padding: 0;
}
</style>