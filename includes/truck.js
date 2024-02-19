document.addEventListener("DOMContentLoaded", function() {
  // Selecteer de knop en animatie-onderdelen in de DOM
  const button = document.querySelector(".truck-button");
  const truck = document.querySelector(".truck");
  const box = document.querySelector(".box");

  // Voeg de 'animation' klasse toe aan de knop wanneer de pagina geladen is
  button.classList.add("animation");

  // Controleer of de 'animation' klasse bestaat op de knop
  if (button.classList.contains("animation")) {
    // Stel initiële waarden in voor de animatie met GSAP (GreenSock Animation Platform)
    gsap.set(button, {
      "--box-s": 1,          // Box grootte
      "--box-o": 1,          // Box doorzichtigheid
      "--truck-y": 1,        // Truck Y-coördinaat
      "--truck-y-n": -25     // Truck Y-coördinaat negatief (voor negatieve animatie)
    });

    // Animeer de box naar zijn startpositie
    gsap.to(box, {
      x: 0,                   // X-coördinaat
      duration: 1,            // Duratie van 1 sec
      ease: "power2.out"      // Animatie verloop curve
    });

    // Animeer de schaduw en positie van de box tijdens het bewegen
    gsap.to(button, {
      "--hx": -5,             // Schaduw X-coördinaat
      "--bx": 50,             // Achtergrond X-positie
      duration: 0.18,         // Duratie
      delay: 0.92             // Vertraging
    });

    // Een korte animatie om de box naar beneden te bewegen
    gsap.to(box, {
      y: 0,                   // Y-coördinaat
      duration: 0.1,          // Duratie van 0.1 sec
      delay: 1.15             // Vertraging
    });

    // Stel de truck Y-coördinaten terug naar de originele waarden
    gsap.set(button, {
      "--truck-y": 0,         // Reset de Y positie van de truck
      "--truck-y-n": -26      // Reset de Y-coördinaat negatief
    });

    // Start de hoofdanimatie voor de truck beweging
    gsap.to(button, {
      "--truck-y": 1,
      "--truck-y-n": -25,
      duration: 0.2,
      delay: 1.25,
      onComplete() {
        // Timelijn voor de verdere beweging van de truck
        gsap.timeline({
          onComplete() {
            // Zodra de animatie voltooid is, voeg de 'done' klasse toe
            button.classList.add("done");
          }
        })
          // Animeer de truck langs verschillende punten
          .to(truck, {
            x: 0,
            duration: 0.4
          })
          .to(truck, {
            x: 40,
            duration: 1
          })
          .to(truck, {
            x: 20,
            duration: 0.6
          })
          .to(truck, {
            x: 96,
            duration: 0.4
          });

        // Animeer de voortgangsindicator van de knop
        gsap.to(button, {
          "--progress": 1,
          duration: 2.4,
          ease: "power2.in"
        });
      }
    });
  } else {
    // Als de animatieklasse niet op de knop zit, reset dan de animatiewaarden
    button.classList.remove("animation", "done");
    gsap.set(truck, {
      x: 4                   // X-coördinaat
    });
    gsap.set(button, {
      "--progress": 0,        // Reset de voortgangsindicator
      "--hx": 0,              // Reset de schaduw X-coördinaat
      "--bx": 0,              // Reset de achtergrond X-positie
      "--box-s": 0.5,         // Reset de box grootte naar 0.5
      "--box-o": 0,           // Reset de doorzichtigheid van de box
      "--truck-y": 0,         // Reset de Y-positie van de truck
      "--truck-y-n": -26      // Reset de Y-coördinaat negatief
    });
    gsap.set(box, {
      x: -24,                 // Reset de X-positie van de box
      y: -6                   // Reset de Y-positie van de box
    });
  }
});