function searchIdeas() {
    let input = document.getElementById("search").value.toLowerCase();
    let cards = document.getElementsByClassName("idea-card");

    for (let i = 0; i < cards.length; i++) {
        let cardText = cards[i].innerText.toLowerCase();
        if (cardText.includes(input)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}
