# Permissions System #

This is a generic permissions system.  The idea is to programatically allow/deny 
access to anything based on user, group, and "other" permissions.

If you understand Linux filesystem permissions, you should understand this system 
intrisically.  It is based upon that system.

This system denies access by default: if a request is made for which there is no 
rule, permission is denied.  This is a pretty basic system, lacking formal tie-ins 
to other tables.  This simplicity is by design: avoiding any unnecessary linkage 
to other tables ensures maximum usability with minimal barrier to entry.

# How It Works

## Basics

The thing that needs to have permissions assigned is stored in the `object` field.
The user that owns it is assigned with the `user_id` field as an integer.  The 
group that owns it is assigned with the `group_id` field as an integer.  When 
requesting permission, the default is to deny: if no object matches the query, 
it is assumed that the permissions are `000`.

There is no concept of parent/child relationships, so each object is considered 
a stand-alone entity.  It should be fairly easy to extend this system to 
accomodate that concept.

## Perms Field

The `perms` field is a number that indicates *user*, *group*, and *other* permissions, 
all together.  So, given the value `321`, the `3` indicates *user* permissions, 
the `2` indicates group permissions, and the `1` indicates *other*.

Values for these fields are as follows:

 * `1` is for *EXECUTE* (with `x` used for shorthand) privilege.
 * `2` is for *WRITE* (with `w` used for shorthand) privilege.
 * `3` is for *READ* (with `r` as shorthand) privilege.

The allowed privileges are added together to show what is allowed and what isn't.
The breakdown is as follows

 * `0` == `---` access denied (no read, no write, no execute)
 * `1` == `--x` (no read, no write, +execute)
 * `2` == `-w-` (no read, +write, no execute)
 * `3` == `-wx` (no read, +write, +execute)
 * `4` == `r--` (+read, no write, no execute)
 * `5` == `r-x` (+read, no write, +execute)
 * `6` == `rw-` (+read, +write, no execute)
 * `7` == `rwx` full access (+read, +write, +execute)

So, to expand on that, you can read the following values as:

 * `777` == full access to owner, group, and other (`rwxrwxrwx`)
 * `532` == read+execute for owner, write+execute for group, write for other (`r-x-wx-w-`)
 * `007` == no access to user/group, full access to other (`------rwx`)
 * `700` == user has full access, but nobody else does (`rwx------`)

## Order of Importance

It's somewhat important to know the order in which permissions are determined. 
So here it is.

1. *user*: if the `user_id` matches, the first set of permissions (the left-most set) are used.
1. *group*: if the `group_id` matches (and `user_id` does not), the group permissions are used.
1. *other*: if neither `user_id` nor `group_id` match, the other permissions are used.


# Example Usage

*TODO*: put in some examples.