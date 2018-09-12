# q2a-donation
Donation plugin for [Question2Answer](https://www.question2answer.org/) (Q2A) which is a free and open source platform for Q&A sites.

This donation plugin is dependant on the [qa-waves-pay](https://github.com/chelahmy/q2a-waves-pay) plugin.

The plugin allows anyone to donate points to members of Q2A sites by buying the points with [Waves](https://wavesplatform.com/) tokens.

## Features
- Allow anyone to donate points to members of Q2A sites.
- Allow anyone to buy points with Waves tokens.
- Separate management of the donation points from the system points.
- Show member's total points inclusive of the donation points.
- Implement a *Donate* link next to a member's points.
- Configurable token-per-point rates.

## Installation
- Install the [qa-waves-pay](https://github.com/chelahmy/q2a-waves-pay) plugin and make the necessary configuration.
- Download and extract the donation plugin files into the Q2A plugin folder *qa-plugin/donation*.

## Configurations
- In the Q2A *Admin* menu click the *Donation Point Rates* sub-menu.
- Click the *Add Rate* button.
- Select an *Asset name*.
- Enter the *Rate per point* for the selected asset.
- Click the *Add* button.
- Add more rates as required.

## How to Donate
- Click on the *Donate* link next to a member's points.
- Enter the number of points to donate.
- Click the *Donate* button.
- Select the amount of tokens to pay for the points.
- Click the *Pay* button.
- The user will be redirected to the Waves client to complete the payment.
- The user will be redirected back to Q2A with a thank you note.
