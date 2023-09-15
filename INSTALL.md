# Want to have these statistics for yourself?

It is possible to create all these stats for your own Strava profile.
Just follow the steps below. If you experience any issues with any of the steps,
feel free to [open an issue](https://github.com/robiningelbrecht/strava-activities/issues/new). I'll be glad to help you out 💅.

## What you'll need

* A Strava API key
* A GitHub account

## Installation

* Make sure your logged in with your GitHub account
* Start off by showing some ❤️ and give this repo a star
* [Fork this repository](https://github.com/robiningelbrecht/strava-activities/fork)
  (forking is fancy word for copying)
* The first thing you'll want to do is delete all my personal data. You don't want these mixed up
  with your own.
  * Navigate to https://github.com/[YOUR-GITHUB-USERNAME]/[REPOSITORY-NAME]/tree/master/files 
    and delete the `activities` and `challenges` directories:
    ![Delete directory](files/install/delete-directories.png)
    To confirm, click `Commit changes`
  * Navigate to https://github.com/[YOUR-GITHUB-USERNAME]/[REPOSITORY-NAME]/tree/master/database
    and do the same for all three subdirectories `activities`, `challenges`and `gears`
* Navigate to your newly created repository `Actions secrets and variables` page (https://github.com/[YOUR-GITHUB-USERNAME]/[REPOSITORY-NAME]/settings/secrets/actions)
  Keep this page open, you will need to add several secrets here
* Next, navigate to your [Strava API settings page](https://www.strava.com/settings/api).
  Copy the `client ID` and `client secret`
  ![Strava API keys](files/install/strava-api-keys.png)
* Create two new repository secrets
![Repo secrets](files/install/repository-secrets.png)
    * __name__: STRAVA_CLIENT_ID, __value__: `client ID` copied from Strava API settings page
    * __name__: STRAVA_CLIENT_SECRET, __value__: `client secret` copied from Strava API settings page
* Now you need to obtain a `Strava API refresh token`. This might be the hardest step.
    * Navigate to https://developers.strava.com/docs/getting-started/#d-how-to-authenticate
      and scroll down to "_For demonstration purposes only, here is how to reproduce the graph above with cURL:_"
    * Follow the 11 steps explained there
    * Make sure you set the `scope` in step 2 to `activity:read_all` to make sure your refresh token has access to all activities
      ![Refresh token](files/install/strava-refresh-token.png)
    * Create a repository secret with the refresh token you obtained: __name__: STRAVA_REFRESH_TOKEN, __value__: The `refresh token` you just obtained
* The last thing you need to do is edit the `update-strava-activities.yml` file:
    * Navigate to https://github.com/[YOUR-GITHUB-USERNAME]/[REPOSITORY-NAME]/edit/master/.github/workflows/update-strava-activities.yml
    * Scroll down to
    ```yml
    name: Commit and push changes
    run: |
      git config --global user.name 'YOUR_GITHUB_USERNAME'
      git config --global user.email 'YOUR_GITHUB_USERNAME@users.noreply.github.com'
      git add .
      git status
      git diff --staged --quiet || git commit -m"Updated strava activities"
      git push
    ```

    * Replace `YOUR_GITHUB_USERNAME` with your own username
    * Remove this code block completely

    ```yml
    name: ntfy.sh
    uses: robiningelbrecht/ntfy-action@v1.0.0
    if: always()
    with:
    url: ${{ secrets.NTFY_URL }}
    topic: ${{ secrets.NTFY_TOPIC }}
    icon: 'https://github.githubassets.com/images/modules/profile/achievements/starstruck-default.png'
    job_status: ${{ job.status }}
    ```

    * Click `commit changes` at the top right-hand corner

## Some things to consider

* Only (virtual) bike rides are imported, other sports are not relevant for these stats
* Because of technical (Strava) limitations, not all Strava challenges
  can be imported. Only the visible ones on your public profile can be imported
* Strava statistics will be re-calculated once a day. If you want to
  re-calculate these manually, navigate to https://github.com/[YOUR-GITHUB-USERNAME]/[REPOSITORY-NAME]/actions/workflows/update-strava-activities.yml
  and click `Run workflow` at the right-hand side
* Running the import for the first time can take a while, depending on how many activities you have on Strava.
  Strava's API has a rate limit of 100 request per 15 minutes and a 1000 requests per day. We have to make sure 
  this limit is not exceeded. See https://developers.strava.com/docs/rate-limits/. If you have more than 500 activities,
  you might run into the daily rate limit. If you do so, the app will import the remaining activities the next day(s).
* Features still to add / finish
    * Create fancy HTML version
    * Display per activity weather data

## 💡Feature request?

For any feedback, help or feature requests, please [open a new issue](https://github.com/robiningelbrecht/strava-activities/issues/new)

