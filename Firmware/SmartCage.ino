#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include "DHT.h"

// ================= EDIT WIFI HERE =================
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
// ==================================================

WebServer server(80);

// Pin Definitions
#define RELAY_DINAMO 26
#define RELAY_LAMPU 27
#define MQ_PIN 34
#define DHTPIN 4
#define DHTTYPE DHT11
#define SERVO_PIN 13

// Constants
#define RELAY_ON  HIGH
#define RELAY_OFF LOW
#define THRESHOLD 1500
#define TEMP_NYALA 33.0
#define TEMP_MATI 30.0

DHT dht(DHTPIN, DHTTYPE);
Servo myServo;

// State Variables
bool isAutoMode = true;
// Manual override states (used when isAutoMode is false, or to reflect current state)
int servoState = -1; // -1: undefined/idle

void handleRoot() {
  server.send(200, "text/plain", "Smart Cage ESP32 is Online");
}

void handleData() {
  float h = dht.readHumidity();
  float t = dht.readTemperature();
  int mq = analogRead(MQ_PIN);
  
  // JSON response
  String json = "{";
  json += "\"temperature\":" + String(isnan(t) ? 0 : t) + ",";
  json += "\"humidity\":" + String(isnan(h) ? 0 : h) + ",";
  json += "\"gas\":" + String(mq) + ",";
  json += "\"auto_mode\":" + String(isAutoMode ? "true" : "false") + ",";
  json += "\"relay_lampu\":" + String(digitalRead(RELAY_LAMPU) == RELAY_ON ? "true" : "false") + ",";
  json += "\"relay_dinamo\":" + String(digitalRead(RELAY_DINAMO) == RELAY_ON ? "true" : "false");
  json += "}";
  
  server.send(200, "application/json", json);
}

void handleControl() {
  // CORS for development (allow all)
  server.sendHeader("Access-Control-Allow-Origin", "*");
  
  if (server.hasArg("mode")) {
    String mode = server.arg("mode");
    if (mode == "auto") isAutoMode = true;
    else if (mode == "manual") isAutoMode = false;
    server.send(200, "text/plain", "Mode updated");
    return;
  }
  
  if (isAutoMode) {
    server.send(400, "text/plain", "Cannot control manually while in Auto Mode");
    return;
  }

  if (server.hasArg("device") && server.hasArg("state")) {
    String device = server.arg("device");
    String state = server.arg("state");
    bool isOn = (state == "on");

    if (device == "lampu") {
      digitalWrite(RELAY_LAMPU, isOn ? RELAY_ON : RELAY_OFF);
    } else if (device == "dinamo") {
      digitalWrite(RELAY_DINAMO, isOn ? RELAY_ON : RELAY_OFF);
    } else if (device == "feeder") {
      // Simple servo sweep for feeder
      if (isOn) {
         myServo.write(90); delay(1000);
         myServo.write(30); delay(1000);
      }
    }
    server.send(200, "text/plain", "OK");
  } else {
    server.send(400, "text/plain", "Missing args");
  }
}

void setup() {
  Serial.begin(115200);

  // Init Components
  myServo.attach(SERVO_PIN);
  dht.begin();
  pinMode(RELAY_DINAMO, OUTPUT);
  pinMode(RELAY_LAMPU, OUTPUT);
  
  digitalWrite(RELAY_DINAMO, RELAY_OFF);
  digitalWrite(RELAY_LAMPU, RELAY_OFF);

  // WiFi Setup
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected! IP address: ");
  Serial.println(WiFi.localIP());

  // Web Server Routes
  server.on("/", handleRoot);
  server.on("/data", HTTP_GET, handleData);
  server.on("/control", HTTP_GET, handleControl); // Using GET for simplicity in dashboard
  server.on("/control", HTTP_POST, handleControl); // Support POST too
  server.enableCORS(true); // Helper if available, otherwise headers needed manually
  server.begin();
}

void loop() {
  server.handleClient();
  
  if (isAutoMode) {
    float h = dht.readHumidity();
    float t = dht.readTemperature();
    
    if (!isnan(t)) {
      if (t > TEMP_NYALA) {
        // High temp logic
        // Only trigger if not already handled or simple state check? 
        // For this simple logic, we just run the blocking sequence which might block webserver...
        // Ideally we should avoid delay(), but sticking to user's logic for now with small modification
        // We shouldn't block for 6 seconds every loop.
        
        static unsigned long lastTrigger = 0;
        if (millis() - lastTrigger > 10000) { // limit frequency
           Serial.println("Auto Logic Triggered");
           myServo.write(90); delay(1000);
           myServo.write(30); delay(1000);
           
           digitalWrite(RELAY_DINAMO, RELAY_ON);
           delay(2000); // Blocks server! 
           
           digitalWrite(RELAY_LAMPU, RELAY_OFF);
           lastTrigger = millis();
        }
      } else {
         // Normal/Cooldown logic
         digitalWrite(RELAY_DINAMO, RELAY_OFF);
         digitalWrite(RELAY_LAMPU, RELAY_ON); // Lamp on when cold? (Based on user code logic)
      }
    }
  }
}
