from flask import Flask, redirect, url_for, session, request, jsonify, render_template
from flask_sqlalchemy import SQLAlchemy
from flask_mail import Mail, Message
from flask_bcrypt import Bcrypt
from oauthlib.oauth2 import WebApplicationClient
import pyotp
import os
import requests

app = Flask(__name__)
app.secret_key = 'your_secret_key'

# Database configuration
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///users.db'
db = SQLAlchemy(app)
bcrypt = Bcrypt(app)

# Email configuration
app.config['MAIL_SERVER'] = 'smtp.example.com'
app.config['MAIL_PORT'] = 587
app.config['MAIL_USERNAME'] = 'your_email@example.com'
app.config['MAIL_PASSWORD'] = 'your_password'
app.config['MAIL_USE_TLS'] = True
app.config['MAIL_USE_SSL'] = False
mail = Mail(app)

# OAuth configurations
GOOGLE_CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID'
GOOGLE_CLIENT_SECRET = 'YOUR_GOOGLE_CLIENT_SECRET'
GOOGLE_DISCOVERY_URL = 'https://accounts.google.com/.well-known/openid-configuration'

FACEBOOK_CLIENT_ID = 'YOUR_FACEBOOK_CLIENT_ID'
FACEBOOK_CLIENT_SECRET = 'YOUR_FACEBOOK_CLIENT_SECRET'
FACEBOOK_DISCOVERY_URL = 'https://www.facebook.com/.well-known/openid-configuration'

# Models
class User(db.Model):
    user_id = db.Column(db.Integer, primary_key=True)
    google_id = db.Column(db.String(255), unique=True)
    facebook_id = db.Column(db.String(255), unique=True)
    name = db.Column(db.String(100), nullable=False)
    user_adrs = db.Column(db.String(100))
    username = db.Column(db.String(60), unique=True, nullable=False)
    email = db.Column(db.String(100), unique=True, nullable=False)
    password = db.Column(db.String(64), nullable=False)
    hashed_password = db.Column(db.String(64), nullable=False)
    date = db.Column(db.DateTime, nullable=False)
    status = db.Column(db.String(10), default='0')
    reset_code = db.Column(db.String(32))
    delete_token = db.Column(db.String(64))
    delete_token_expiration = db.Column(db.DateTime)
    picture = db.Column(db.String(100))
    user_log = db.Column(db.String(20))
    lastIp = db.Column(db.String(200))
    logs = db.Column(db.Text)
    cookieUpdate = db.Column(db.String(200))
    gender = db.Column(db.String(10))

db.create_all()

# OAuth clients
google_client = WebApplicationClient(GOOGLE_CLIENT_ID)
facebook_client = WebApplicationClient(FACEBOOK_CLIENT_ID)

@app.route('/')
def home():
    return render_template('home.html')

@app.route('/login')
def login():
    return render_template('login.html')

@app.route('/login/google')
def login_google():
    google_provider_cfg = requests.get(GOOGLE_DISCOVERY_URL).json()
    authorization_endpoint = google_provider_cfg["authorization_endpoint"]
    request_uri = google_client.prepare_request_uri(
        authorization_endpoint,
        redirect_uri=request.base_url + "/callback",
        scope=["openid", "email", "profile"],
    )
    return redirect(request_uri)

@app.route('/login/google/callback')
def callback_google():
    code = request.args.get("code")
    google_provider_cfg = requests.get(GOOGLE_DISCOVERY_URL).json()
    token_endpoint = google_provider_cfg["token_endpoint"]
    token_url, headers, body = google_client.prepare_token_request(
        token_endpoint,
        authorization_response=request.url,
        redirect_url=request.base_url,
        code=code
    )
    token_response = requests.post(
        token_url,
        headers=headers,
        data=body,
        auth=(GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET),
    )
    google_client.parse_request_body_response(token_response.text)
    userinfo_endpoint = google_provider_cfg["userinfo_endpoint"]
    uri, headers, body = google_client.add_token(userinfo_endpoint)
    userinfo_response = requests.get(uri, headers=headers, data=body)

    user_info = userinfo_response.json()
    user = User.query.filter_by(google_id=user_info["sub"]).first()
    if not user:
        user = User(
            google_id=user_info["sub"],
            name=user_info["name"],
            email=user_info["email"],
            picture=user_info["picture"]
        )
        db.session.add(user)
        db.session.commit()

    session['user_id'] = user.user_id
    return redirect(url_for('profile'))

@app.route('/login/facebook')
def login_facebook():
    facebook_provider_cfg = requests.get(FACEBOOK_DISCOVERY_URL).json()
    authorization_endpoint = facebook_provider_cfg["authorization_endpoint"]
    request_uri = facebook_client.prepare_request_uri(
        authorization_endpoint,
        redirect_uri=request.base_url + "/callback",
        scope=["email"],
    )
    return redirect(request_uri)

@app.route('/login/facebook/callback')
def callback_facebook():
    code = request.args.get("code")
    facebook_provider_cfg = requests.get(FACEBOOK_DISCOVERY_URL).json()
    token_endpoint = facebook_provider_cfg["token_endpoint"]
    token_url, headers, body = facebook_client.prepare_token_request(
        token_endpoint,
        authorization_response=request.url,
        redirect_url=request.base_url,
        code=code
    )
    token_response = requests.post(
        token_url,
        headers=headers,
        data=body,
        auth=(FACEBOOK_CLIENT_ID, FACEBOOK_CLIENT_SECRET),
    )
    facebook_client.parse_request_body_response(token_response.text)
    userinfo_endpoint = facebook_provider_cfg["userinfo_endpoint"]
    uri, headers, body = facebook_client.add_token(userinfo_endpoint)
    userinfo_response = requests.get(uri, headers=headers, data=body)

    user_info = userinfo_response.json()
    user = User.query.filter_by(facebook_id=user_info["id"]).first()
    if not user:
        user = User(
            facebook_id=user_info["id"],
            name=user_info["name"],
            email=user_info["email"],
            picture=user_info["picture"]["data"]["url"]
        )
        db.session.add(user)
        db.session.commit()

    session['user_id'] = user.user_id
    return redirect(url_for('profile'))

@app.route('/profile')
def profile():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    user = User.query.get(session['user_id'])
    return render_template('profile.html', user=user)

@app.route('/password-reset', methods=['GET', 'POST'])
def password_reset():
    if request.method == 'POST':
        email = request.form['email']
        user = User.query.filter_by(email=email).first()
        if user:
            reset_code = pyotp.random_base32()
            user.reset_code = reset_code
            db.session.commit()

            msg = Message('Password Reset', sender='your_email@example.com', recipients=[email])
            msg.body = f'Your password reset code is {reset_code}'
            mail.send(msg)
        return redirect(url_for('password_reset_confirm'))
    return render_template('password_reset.html')

@app.route('/password-reset/confirm', methods=['GET', 'POST'])
def password_reset_confirm():
    if request.method == 'POST':
        email = request.form['email']
        reset_code = request.form['reset_code']
        new_password = request.form['new_password']
        user = User.query.filter_by(email=email, reset_code=reset_code).first()
        if user:
            user.password = bcrypt.generate_password_hash(new_password).decode('utf-8')
            user.reset_code = None
            db.session.commit()
            return redirect(url_for('login'))
    return render_template('password_reset_confirm.html')

@app.route('/2fa-setup')
def two_factor_setup():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    user = User.query.get(session['user_id'])
    if not user:
        return redirect(url_for('login'))
    totp = pyotp.TOTP(pyotp.random_base32())
    session['totp_secret'] = totp.secret
    uri = totp.provisioning_uri(name=user.email, issuer_name='YourApp')
    return render_template('2fa_setup.html', uri=uri)

@app.route('/2fa-verify', methods=['POST'])
def two_factor_verify():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    user = User.query.get(session['user_id'])
    if not user:
        return redirect(url_for('login'))
    totp = pyotp.TOTP(session['totp_secret'])
    token = request.form['token']
    if totp.verify(token):
        user.user_log = '2FA Enabled'
        db.session.commit()
        return redirect(url_for('profile'))
    return redirect(url_for('two_factor_setup'))

if __name__ == '__main__':
    app.run(debug=True)
