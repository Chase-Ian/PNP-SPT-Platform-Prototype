<!-- resources/js/Pages/Legal/Privacy.vue
     Privacy Notice under the Data Privacy Act of 2012 (RA 10173). Describes the
     platform as it actually behaves: update it whenever data handling changes,
     and bump LEGAL_VERSION so users are asked to review it again. -->
<script setup>
import LegalLayout from '@/Layouts/LegalLayout.vue';
import LegalValue from '@/Components/LegalValue.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    version: String,
    effectiveDate: String,
    draft: Boolean,
    controller: Object,
    dpo: Object,
    aiAssistant: Boolean,
    processors: Object,
    twoFactorScope: String, // none | staff | all (REQUIRE_2FA)
});

const twoFactorText = computed(() => ({
    all: 'two-factor authentication is required for every account;',
    staff: 'two-factor authentication is required for Administrators and Supervisors, and available to every user;',
}[props.twoFactorScope] ?? 'two-factor authentication is available to every user;'));
</script>

<template>
    <LegalLayout title="Privacy Notice" :version="version" :effective-date="effectiveDate" :draft="draft">
        <p>
            This notice explains how the Specialized Personnel Training Platform (the "Platform") collects, uses,
            shares and protects your personal data, and what rights you have under the Data Privacy Act of 2012
            (Republic Act No. 10173) and its implementing rules.
        </p>

        <h2>Who we are</h2>
        <p>
            The {{ controller.name }}, through the <LegalValue :value="controller.office" />
            (<LegalValue :value="controller.address" />), is the personal information controller for the Platform.
            Our Data Protection Officer (DPO) can be reached at <LegalValue :value="dpo.email" /> or
            <LegalValue :value="dpo.phone" />.
        </p>

        <h2>The personal data we collect</h2>
        <table>
            <thead>
                <tr><th>Category</th><th>What</th><th>Source</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Account details</td>
                    <td>First and last name, rank, official email address, unit or office, police regional office, and role (trainee, supervisor or administrator)</td>
                    <td>You, at sign-up and on your profile; an Administrator, for staff accounts</td>
                </tr>
                <tr>
                    <td>Sign-in and security</td>
                    <td>Your password (stored only as a one-way hash), two-factor authentication secret and recovery codes (encrypted), email verification status, account lock status, and failed sign-in counters (kept for about a minute)</td>
                    <td>You and the Platform</td>
                </tr>
                <tr>
                    <td>Agreement records</td>
                    <td>Which version of the Terms of Service and this notice you accepted, when, and your IP address at that moment</td>
                    <td>The Platform</td>
                </tr>
                <tr>
                    <td>Training records</td>
                    <td>Enrolments, lesson progress, module completions, assessment attempts (your answers, score, and pass or fail), and certificates (serial number, control number and issue date)</td>
                    <td>The Platform, as you train</td>
                </tr>
                <tr>
                    <td>Requests and messages</td>
                    <td>Email change requests (current and new address, your reason, and the Administrator's decision and note) and in-app notifications</td>
                    <td>You and Administrators</td>
                </tr>
                <tr>
                    <td>Technical data</td>
                    <td>Session cookies; the IP address and browser details held with your session; and server logs, which can record your account number and security events</td>
                    <td>The Platform</td>
                </tr>
            </tbody>
        </table>
        <p>
            Assessment results and other training records can count as information about your education, which
            RA 10173 treats as sensitive personal information. We protect all of the data above accordingly.
        </p>
        <p>
            We do not ask for government ID numbers, health information or other sensitive information. Please do not
            enter it anywhere on the Platform.
        </p>

        <h2>Why we use it</h2>
        <ul>
            <li>to create and manage your account and to confirm who you are (email verification, two-factor authentication and account locks);</li>
            <li>to deliver training, track your progress and grade your assessments;</li>
            <li>to issue certificates and let others check that they are genuine;</li>
            <li>to let Supervisors and Administrators monitor training completion and readiness;</li>
            <li>to handle your requests, such as email changes, and to send account emails such as verification links, password resets and security notices;</li>
            <li>to keep the Platform secure, investigate incidents and keep an audit trail;</li>
            <li>to produce training statistics, using combined data that does not identify you wherever possible;</li>
            <li>to meet our legal and regulatory obligations.</li>
        </ul>
        <p>
            We do not sell your data or use it for advertising. Apart from grading assessments against an answer key,
            we do not make decisions about you by automated means alone.
        </p>

        <h2>Our legal basis</h2>
        <p>
            We process your personal data because it is necessary for the PNP's training and capability-development
            functions as a public authority, and to comply with our legal obligations (RA 10173, Section 12(c) and (e)).
            For sensitive personal information such as training results, we rely on the processing being provided for by
            the laws and regulations that govern PNP personnel training (Section 13(b)):
            <mark class="legal-todo">[DPO to list the specific laws, rules and PNP issuances]</mark>.
        </p>
        <p>
            Agreeing to this notice when you sign up confirms that you were informed. If we ever ask for your consent
            to something else, you can withdraw it at any time.
        </p>

        <h2>Who can see your data</h2>
        <ul>
            <li><strong>You</strong> can see your own profile, progress, results and certificates.</li>
            <li><strong>Supervisors</strong> can see trainees' names, units, progress, assessment results and certificate status.</li>
            <li><strong>Administrators</strong> can see account details and status, training records and email change requests. They can lock accounts and reset two-factor authentication.</li>
            <li>
                <strong>Anyone with a certificate's serial number or QR code</strong> can see, on the public verification page,
                the holder's name, the course, the issue date, the holder's unit or office and region, and the certificate's
                serial and control numbers.
            </li>
            <li><strong>Service providers</strong> that run the Platform for us. They may use the data only on our instructions:</li>
        </ul>
        <table>
            <thead>
                <tr><th>Service</th><th>Provider and location</th></tr>
            </thead>
            <tbody>
                <tr><td>Application hosting</td><td><LegalValue :value="processors.hosting" /></td></tr>
                <tr><td>Database</td><td><LegalValue :value="processors.database" /></td></tr>
                <tr><td>File storage (certificate PDFs and lesson files)</td><td><LegalValue :value="processors.storage" /></td></tr>
                <tr><td>Email delivery</td><td><LegalValue :value="processors.email" /></td></tr>
                <tr><td>Web fonts (receives your IP address when pages load)</td><td>Bunny Fonts (bunny.net)</td></tr>
            </tbody>
        </table>
        <ul>
            <li><strong>Government authorities</strong>, when the law requires it, for example under a court order.</li>
        </ul>

        <h2>Transfers outside the Philippines</h2>
        <p>
            Some of the providers above store or process data outside the Philippines. We remain responsible for your
            data when they do (RA 10173, Section 21), and we require them by contract to protect it at least as well as
            Philippine law requires. <mark class="legal-todo">[Confirm a data processing agreement is in place with each provider]</mark>
        </p>

        <h2>How long we keep it</h2>
        <table>
            <thead>
                <tr><th>Data</th><th>How long</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Account details, training records and certificates</td>
                    <td>While your account exists. Where PNP records rules require training records to be kept longer: <mark class="legal-todo">[period under the PNP records disposition schedule]</mark></td>
                </tr>
                <tr><td>Email change requests</td><td><mark class="legal-todo">[retention period]</mark></td></tr>
                <tr><td>Agreement records</td><td>While your account exists</td></tr>
                <tr><td>Signed-in sessions</td><td>Until you sign out, or after 2 hours without activity. "Remember me" keeps you signed in longer on that device.</td></tr>
                <tr><td>Links sent by email</td><td>Verification and password reset links expire after 60 minutes; two-factor reset links after 30 minutes</td></tr>
                <tr><td>Server logs</td><td><mark class="legal-todo">[retention period]</mark></td></tr>
                <tr><td>Backups</td><td><mark class="legal-todo">[backup retention period]</mark></td></tr>
            </tbody>
        </table>
        <p>
            If you delete your account, your profile, training records and certificates are permanently removed from
            the Platform right away, and your certificates can no longer be verified. Copies in backups are removed when
            those backups expire.
        </p>

        <h2>How we protect it</h2>
        <ul>
            <li>connections to the Platform are encrypted (HTTPS);</li>
            <li>passwords are stored only as one-way hashes, and two-factor authentication secrets are encrypted;</li>
            <li>{{ twoFactorText }}</li>
            <li>email addresses are verified, sign-in attempts are limited, and accounts can be locked;</li>
            <li>access is limited by role, and only an Administrator can change an account's email address;</li>
            <li>security events such as two-factor resets and email changes are logged.</li>
        </ul>
        <p>
            If a personal data breach is likely to put you at risk, we will notify the National Privacy Commission and
            the affected users within 72 hours of learning of it, as NPC Circular 16-03 requires.
        </p>

        <h2>Your rights</h2>
        <p>Under RA 10173, you have the right to:</p>
        <ul>
            <li>be informed of how your data is processed (this notice);</li>
            <li>access your personal data;</li>
            <li>object to processing, and withdraw your consent where we rely on it;</li>
            <li>have inaccurate data corrected: you can update your name, rank, unit and region on your profile, and change your email address through an Administrator;</li>
            <li>have your data deleted or blocked when it is no longer needed or was processed unlawfully;</li>
            <li>receive a copy of your data in a commonly used electronic format (data portability);</li>
            <li>be compensated for damage caused by inaccurate or unlawfully used data;</li>
            <li>file a complaint with the National Privacy Commission.</li>
        </ul>
        <p>
            Some rights may be limited where the law allows, for example when records must be kept by law. To use your
            rights, contact the DPO at <LegalValue :value="dpo.email" />. We may first need to confirm your identity.
            We will respond within <mark class="legal-todo">[response period]</mark>.
        </p>
        <p>
            National Privacy Commission: <a href="https://privacy.gov.ph" target="_blank" rel="noopener">privacy.gov.ph</a>,
            complaints@privacy.gov.ph.
        </p>

        <h2>Cookies</h2>
        <p>The Platform uses only the cookies it needs to work:</p>
        <table>
            <thead>
                <tr><th>Cookie</th><th>Purpose</th></tr>
            </thead>
            <tbody>
                <tr><td>Session cookie</td><td>Keeps you signed in during a visit</td></tr>
                <tr><td>XSRF-TOKEN</td><td>Protects forms against cross-site request forgery</td></tr>
                <tr><td>remember_web_…</td><td>Keeps you signed in on that device if you tick "Remember me"</td></tr>
            </tbody>
        </table>
        <p>We do not use advertising or analytics cookies.</p>

        <template v-if="aiAssistant">
            <h2>AI lesson assistant</h2>
            <p>
                The lesson assistant runs on an AI model hosted on the Platform's own servers. Your questions are not
                sent to an outside AI company and are not saved to your account. Technical logs about how the assistant
                handled a question can include parts of it, and are kept for
                <mark class="legal-todo">[log retention period]</mark>. Do not type personal, sensitive or classified
                information into the assistant.
            </p>
        </template>

        <h2>Changes to this notice</h2>
        <p>
            When we change this notice, we will update the version and effective date shown above and ask you to review
            it the next time you use the Platform. The <Link :href="route('legal.terms')">Terms of Service</Link> explain
            the rules for using the Platform.
        </p>

        <h2>Contact us</h2>
        <p>
            Data Protection Officer, <LegalValue :value="controller.office" />, {{ controller.name }},
            <LegalValue :value="controller.address" />. Email <LegalValue :value="dpo.email" />,
            phone <LegalValue :value="dpo.phone" />.
        </p>
    </LegalLayout>
</template>
