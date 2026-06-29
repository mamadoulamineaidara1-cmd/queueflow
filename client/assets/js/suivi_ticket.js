let position = 12;

let waitingTime = 18;

let currentTicket = 18;

const positionNumber =
document.getElementById("positionNumber");

const waitingTimeText =
document.getElementById("waitingTime");

const peopleAhead =
document.getElementById("peopleAhead");

const currentTicketBox =
document.getElementById("currentTicket");

const progressFill =
document.querySelector(".progress-fill");

setInterval(()=>{

    if(position > 1){

        position--;

        waitingTime -= 2;

        currentTicket++;

        positionNumber.textContent =
        position;

        waitingTimeText.textContent =
        waitingTime + " min";

        peopleAhead.textContent =
        (position - 1) +
        " personnes avant vous";

        currentTicketBox.textContent =
        "A0" + currentTicket;

        let progress =
        100 - (position * 5);

        progressFill.style.width =
        progress + "%";
    }

},5000);