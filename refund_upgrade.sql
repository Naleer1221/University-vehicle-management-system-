-- RUN THIS ONCE in phpMyAdmin on an EXISTING vehicle_booking_db.
-- Do NOT reimport database.sql. This keeps all existing users and bookings.
USE vehicle_booking_db;

ALTER TABLE bookings
  ADD COLUMN cancellation_reason VARCHAR(1000) NOT NULL DEFAULT '' AFTER notes;

CREATE TABLE IF NOT EXISTS refund_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  user_id INT NOT NULL,
  reason VARCHAR(1000) NOT NULL,
  requested_amount DECIMAL(12,2) NOT NULL,
  approved_amount DECIMAL(12,2) DEFAULT NULL,
  status ENUM('pending','approved','rejected','refunded') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(1000) NOT NULL DEFAULT '',
  refund_reference VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME DEFAULT NULL,
  refunded_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_refund_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_refund_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_refund_requested CHECK (requested_amount >= 0),
  CONSTRAINT chk_refund_approved CHECK (approved_amount IS NULL OR approved_amount >= 0),
  INDEX idx_refund_status (status, created_at)
) ENGINE=InnoDB;
